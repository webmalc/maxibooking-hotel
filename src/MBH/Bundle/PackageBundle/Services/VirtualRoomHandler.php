<?php

namespace MBH\Bundle\PackageBundle\Services;

use Symfony\Bridge\Monolog\Logger;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\Search;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class VirtualRoomHandler
{
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var  Search $search */
    private $search;
    /** @var Logger $logger */
    private $logger;
    /** @var Translator $translator */
    private $translator;

    public function __construct(DocumentManager $dm, Search $search, Logger $logger, Translator $translator)
    {
        $this->dm = $dm;
        $this->search = $search;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function setVirtualRooms(\DateTime $begin, \DateTime $end)
    {
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $packages =  $packageRepository->fetchWithVirtualRooms($begin, $end)->toArray();
        usort($packages, function ($a, $b) {
            /** @var Package $a */
            /** @var Package $b */
            return ($a->getBegin() < $b->getBegin()) ? -1 : 1;
        });

        $sortedPackages = $this->sortPackagesByRoomTypeAndVirtualRoom($packages);
        $emptyIntervals = $this->getEmptyIntervals($sortedPackages);

        /** @var Package $package */
        foreach ($packages as $package) {
            $packageDatesString = $this->getPackageIntervalString($package->getBegin(), $package->getEnd());
            if (isset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString])) {
                $virtualRoomWithWindow = $emptyIntervals[$package->getRoomType()->getId()][$packageDatesString];
                $package->setVirtualRoom($virtualRoomWithWindow);
                $this->dm->flush();
                unset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString]);
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

    private function setVirtualRoom(Package $package)
    {
        $searchResult = (new SearchResult())
            ->setBegin($package->getBegin())
            ->setEnd($package->getEnd())
            ->setRoomType($package->getRoomType());
        $result = $this->search->setVirtualRoom($searchResult, $package->getTariff(), $package);

        if ($result instanceof SearchResult && $package->getVirtualRoom() != $result->getVirtualRoom()) {
            $package->setVirtualRoom($result->getVirtualRoom());
            $this->logger->info(
                $this->translator->trans('virtual_room_handler.package_virtual_room_changed', [
                    '%package_number%' => $package->getTitle(),
                    '%room_name%' => $result->getVirtualRoom()->getName()
                ]));
            $this->dm->flush();
        }
    }

    private function sortPackagesByRoomTypeAndVirtualRoom($packages)
    {
        $sortedPackages = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            $sortedPackages[$package->getRoomType()->getId()][$package->getVirtualRoom()->getId()] = $package;
        }

        return $sortedPackages;
    }

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

    /**
     * Get empty intervals between middle packages, having same virtual room
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

                    $intervalString = $this->getPackageIntervalString($previous->getEnd(), $current->getBegin());
                    $emptyIntervals[$roomTypeId][$intervalString] = $current->getVirtualRoom();
                }
            }
        }

        return $emptyIntervals;
    }

    private function getPackageIntervalString(\DateTime $begin, \DateTime $end)
    {
        return $begin->format('d.m.Y') . '-' . $end->format('d.m.Y');
    }
}