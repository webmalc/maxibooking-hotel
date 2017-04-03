<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Bridge\Monolog\Logger;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\Search;

class VirtualRoomHandler
{
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var  Search $search */
    private $search;
    /** @var Logger $logger */
    private $logger;

    public function __construct(DocumentManager $dm, Search $search, Logger $logger)
    {
        $this->dm = $dm;
        $this->search = $search;
        $this->logger = $logger;
    }

    /**
     * Set virtual rooms for packages between specified begin and end dates
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function setVirtualRooms(\DateTime $begin, \DateTime $end)
    {
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $packages = $packageRepository->extendedFetchWithVirtualRooms($begin, $end, true)->toArray();

        $sortedPackages = $this->sortPackagesByRoomTypeAndVirtualRoom($packages);
        $emptyIntervals = $this->getEmptyIntervals($sortedPackages);
        $this->logEmptyIntervalsData($emptyIntervals);

        /** @var Package $package */
        foreach ($packages as $package) {
            $packageDatesString = $this->getPackageIntervalString($package->getBegin(), $package->getEnd());
            if (isset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString])
                && !$this->hasBothSideNeighbors($package, $sortedPackages)
            ) {
                /** @var Room $virtualRoomWithWindow */
                $virtualRoomWithWindow = $emptyIntervals[$package->getRoomType()->getId()][$packageDatesString];
                $package->setVirtualRoom($virtualRoomWithWindow);
                $this->dm->flush();
                unset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString]);

                $this->logger->info(
                    "For package \"{$package->getTitle()}\" set virtual room \"{$package->getVirtualRoom()->getName()}\""
                );
            }
        }

        $packagesWithoutVRoom = $packageRepository->getNotVirtualRoom($begin, $end);
        foreach ($packagesWithoutVRoom as $package) {
            $this->setVirtualRoom($package);
        }

        foreach ($packages as $package) {
            if (!$this->hasNeighboringPackages($package, $sortedPackages)) {
                $this->setVirtualRoom($package);
            }
        }
    }

    /**
     * Log data about empty intervals between virtual rooms of neighboring packages
     *
     * @param $emptyIntervals
     */
    private function logEmptyIntervalsData($emptyIntervals)
    {
        foreach ($emptyIntervals as $roomTypeId => $emptyIntervalsByRoomType) {
            /** @var Room $emptyRoom */
            foreach ($emptyIntervalsByRoomType as $emptyIntervalDatesString => $emptyRoom) {
                $this->logger->info(
                    "Virtual room \"{$emptyRoom->getName()}\" with room type ID = \"$roomTypeId\" has empty interval \"$emptyIntervalDatesString\""
                );
            }
        }
    }

    /**
     * Set virtual room for a single package
     *
     * @param Package $package
     */
    private function setVirtualRoom(Package $package)
    {
        $searchResult = (new SearchResult())
            ->setBegin($package->getBegin())
            ->setEnd($package->getEnd())
            ->setRoomType($package->getRoomType());
        $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($package->getRoomType()->getHotel());
        $result = $this->search->setVirtualRoom($searchResult, $baseTariff, $package);

        if ($result instanceof SearchResult && $package->getVirtualRoom() != $result->getVirtualRoom()) {
            $package->setVirtualRoom($result->getVirtualRoom());
            $this->logger->info(
                "For package \"{$package->getTitle()}\" set virtual room \"{$result->getVirtualRoom()->getName()}\""
            );
            $this->dm->flush();
        }
    }

    /**
     * Get sorted packages by room type and virtual room
     *
     * @param $packages
     * @return array
     */
    private function sortPackagesByRoomTypeAndVirtualRoom($packages)
    {
        $sortedPackages = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            $sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()][] = $package;
        }

        return $sortedPackages;
    }

    /**
     * Check whether has package a nearby package
     *
     * @param Package $package
     * @param $sortedPackages
     * @return bool
     */
    public function hasNeighboringPackages(Package $package, $sortedPackages)
    {
        if (isset($sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()])) {
            $neighboringPackages = $sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()];
            /** @var Package $neighboringPackage */
            foreach ($neighboringPackages as $neighboringPackage) {
                if ($neighboringPackage->getBegin() == $package->getEnd()
                    || $neighboringPackage->getEnd() == $package->getBegin()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasBothSideNeighbors(Package $package, array $sortedPackages)
    {
        if (isset($sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()])) {
            /** @var Package[] $neighboringPackages */
            $neighboringPackages =
                $sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()];
            for ($i = 1; $i < count($neighboringPackages); $i++) {
                if ($neighboringPackages[$i] == $package) {
                    continue;
                }
                $previous = $neighboringPackages[$i - 1];
                if ($previous->getEnd() == $package->getBegin()
                    && isset($neighboringPackages[$i + 1])
                    && $neighboringPackages[$i + 1]->getBegin() == $package->getEnd()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get empty intervals between nearby packages, having same virtual room
     *
     * @param Package[] $packages
     * @return array
     */
    public function getEmptyIntervals($packages)
    {
        $emptyIntervals = [];
        foreach ($packages as $roomTypeId => $packagesByRoomType) {
            foreach ($packagesByRoomType as $packagesByVirtualRoom) {

                for ($i = 1; $i < count($packagesByVirtualRoom); $i++) {
                    /** @var Package $previous */
                    $previous = $packagesByVirtualRoom[$i - 1];
                    /** @var Package $current */
                    $current = $packagesByVirtualRoom[$i];
                    $previousPackageEndDate = $previous->getEnd();
                    $currentPackageBeginDate = $current->getBegin();
                    if ($previousPackageEndDate != $currentPackageBeginDate) {
                        $intervalString = $this->getPackageIntervalString($previous->getEnd(), $current->getBegin());
                        $emptyIntervals[$roomTypeId][$intervalString] = $current->getVirtualRoom();
                    }
                }
            }
        }

        return $emptyIntervals;
    }

    /**
     * @param \DateTime $begin
     * @param Room $firstVirtualRoom
     * @param Room $secondVirtualRoom
     * @param $excludedPackage
     */
    public function replaceVirtualRoomChains(
        \DateTime $begin,
        Room $firstVirtualRoom,
        Room $secondVirtualRoom,
        $excludedPackage
    ) {
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $packages = $packageRepository->extendedFetchWithVirtualRooms($begin,
            (clone $begin)->add(new \DateInterval('P2Y')),
            false, null, [$firstVirtualRoom->getId(), $secondVirtualRoom->getId()], $excludedPackage);

        /** @var Package $package */
        foreach ($packages as $package) {
            if ($package->getVirtualRoom() == $firstVirtualRoom) {
                $package->setVirtualRoom($secondVirtualRoom);
            } else {
                $package->setVirtualRoom($firstVirtualRoom);
            }
        }

        $this->dm->flush();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return string
     */
    private function getPackageIntervalString(\DateTime $begin, \DateTime $end)
    {
        return $begin->format('d.m.Y') . '-' . $end->format('d.m.Y');
    }
}