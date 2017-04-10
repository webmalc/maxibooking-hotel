<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Bridge\Monolog\Logger;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\Search;
use Symfony\Component\Translation\Translator;

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

    const HANDLED_PACKAGES_LIMIT = 100;

    public function __construct(DocumentManager $dm, Search $search, Logger $logger, Translator $translator)
    {
        $this->dm = $dm;
        $this->search = $search;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * Set virtual rooms for packages between specified begin and end dates
     *
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param $limit
     * @param $offset
     * @return array Данные о перемещенных бронях. Ключи массива 'package' и 'oldVirtualRoom'
     */
    public function setVirtualRooms(\DateTime $begin, \DateTime $end, $limit, $offset)
    {
        $movedPackagesData = [];

        for ($i = 0; $i < ceil($limit / self::HANDLED_PACKAGES_LIMIT); $i++) {
            $skipNumber = $i * self::HANDLED_PACKAGES_LIMIT + $offset;
            $packages = $this->getLimitPackages($begin, $end, self::HANDLED_PACKAGES_LIMIT, $skipNumber);

            $sortedPackages = $this->sortPackagesByRoomTypeAndVirtualRoom($packages);
            $emptyIntervals = $this->getEmptyIntervals($sortedPackages);
            $this->logEmptyIntervalsData($emptyIntervals);

            /** @var Package $package */
            foreach ($packages as $package) {
                $packageDatesString = $this->getPackageIntervalString($package->getBegin(), $package->getEnd());
                if (isset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString])
                    && !$this->hasBothSideNeighbors($package)
                ) {
                    /** @var Room $virtualRoomWithWindow */
                    $virtualRoomWithWindow = $emptyIntervals[$package->getRoomType()->getId()][$packageDatesString];
                    if ($this->hasSufficientWindows($package, $virtualRoomWithWindow)) {
                        $this->addPackageMovingData($package, $package->getVirtualRoom(), $movedPackagesData);
                        $package->setVirtualRoom($virtualRoomWithWindow);
                        $this->dm->flush();
                        unset($emptyIntervals[$package->getRoomType()->getId()][$packageDatesString]);

                        $this->logger->info(
                            "For package \"{$package->getTitle()}\" set virtual room \"{$package->getVirtualRoom()->getName()}\", hotel name \"{$package->getHotel()->getName()}\" in empty interval"
                        );
                    }
                }
            }

            $packagesWithoutVRoom = $this->dm
                ->getRepository('MBHPackageBundle:Package')
                ->getNotVirtualRoom($begin, $end);
            foreach ($packagesWithoutVRoom as $package) {
                $this->setVirtualRoom($package, $movedPackagesData);
            }

            foreach ($packages as $package) {
                if (!$this->hasNeighboringPackages($package, $sortedPackages)) {
                    $this->setVirtualRoom($package, $movedPackagesData);
                }
            }
            $this->dm->clear();
            $this->dm->flush();
        }

        return $movedPackagesData;
    }

    /**
     * @param Package $package
     * @param Room $oldVirtualRoom
     * @param $movedPackagesData
     */
    private function addPackageMovingData(Package $package, ?Room $oldVirtualRoom, &$movedPackagesData)
    {
        $movedPackagesData[] = [
            'package' => $package,
            'oldVirtualRoom' => $oldVirtualRoom
        ];
    }

    private function getLimitPackages($begin, $end, $limit, $skip)
    {
        return $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->extendedFetchWithVirtualRooms($begin,
                $end,
                true,
                null,
                null,
                null,
                $limit,
                $skip);
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
     * @param Package $package
     * @param Room $virtualRoom
     * @return bool
     */
    public function hasSufficientWindows(Package $package, Room $virtualRoom)
    {
        $roomType = $package->getRoomType();
        $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($package->getRoomType()->getHotel());

        $restrictionRepository = $this->dm->getRepository('MBHPriceBundle:Restriction');
        $beginRestriction = $restrictionRepository->findOneByDate($package->getBegin(), $roomType, $baseTariff);
        $endRestriction = $restrictionRepository->findOneByDate($package->getEnd(), $roomType, $baseTariff);

        $adjoiningPackagesBegin = clone $package->getBegin();
        $adjoiningPackagesEnd = clone $package->getEnd();

        if ($beginRestriction && $beginRestriction->getMinStayArrival()) {
            $adjoiningPackagesBegin = $adjoiningPackagesBegin
                ->modify('-' . ($beginRestriction->getMinStayArrival() - 1) . ' days');
        }

        if ($endRestriction && $endRestriction->getMinStayArrival()) {
            $adjoiningPackagesEnd = $adjoiningPackagesEnd
                ->modify('+' . ($endRestriction->getMinStayArrival() - 1) . ' days');
        }

        $adjoiningPackages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->extendedFetchWithVirtualRooms(
                $adjoiningPackagesBegin,
                $adjoiningPackagesEnd,
                false,
                $roomType,
                [$virtualRoom->getId()],
                $package
            );

        /** @var Package $adjoiningPackage */
        foreach ($adjoiningPackages as $adjoiningPackage) {
            if (!($adjoiningPackage->getBegin() == $package->getEnd() || $adjoiningPackage->getEnd() == $package->getBegin())) {
                $this->logger->info('incorrect attempt to set virtual room for package "'
                    . $package->getNumberWithPrefix() . '", in virtual room "' . $virtualRoom->getName() . '"');
                return false;
            }
        }

        return true;
    }

    /**
     * Set virtual room for a single package
     *
     * @param Package $package
     * @param $movedPackagesData
     */
    private function setVirtualRoom(Package $package, &$movedPackagesData)
    {
        $searchResult = (new SearchResult())
            ->setBegin($package->getBegin())
            ->setEnd($package->getEnd())
            ->setRoomType($package->getRoomType());
        $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($package->getRoomType()->getHotel());
        $result = $this->search->setVirtualRoom($searchResult, $baseTariff, $package);

        if ($result instanceof SearchResult && $package->getVirtualRoom() != $result->getVirtualRoom()) {
            $oldVirtualRoom = $package->getVirtualRoom();
            if ($this->hasSufficientWindows($package, $result->getVirtualRoom())) {
                $package->setVirtualRoom($result->getVirtualRoom());
                $this->logger->info(
                    "For package \"{$package->getTitle()}\" set virtual room \"{$result->getVirtualRoom()->getName()}\", hotel name \"{$package->getHotel()->getName()}\""
                );
                $this->dm->flush();
            }

            $this->addPackageMovingData($package, $oldVirtualRoom, $movedPackagesData);
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

    private function hasBothSideNeighbors(Package $package)
    {
        $adjoiningPackagesCount = $this->dm->getRepository('MBHPackageBundle:Package')
            ->extendedFetchWithVirtualRooms($package->getBegin(), $package->getEnd(), false, null,
                [$package->getVirtualRoom()->getId()], $package)->count();

        return $adjoiningPackagesCount == 2;
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