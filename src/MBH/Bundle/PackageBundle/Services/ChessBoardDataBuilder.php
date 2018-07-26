<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Models\ChessBoard\ChessBoardUnit;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ChessBoardDataBuilder
 * @package MBH\Bundle\PackageBundle\Services
 */
class ChessBoardDataBuilder
{
    /** @var DocumentManager $dm */
    private $dm;
    /** @var  Helper $helper */
    private $helper;
    /** @var  Hotel $hotel */
    private $hotel;
    private $roomTypeIds;
    /** @var  \DateTime $beginDate */
    private $beginDate;
    /** @var  \DateTime $endDate */
    private $endDate;
    /** @var  array $housingIds */
    private $housingIds;
    /** @var  Tariff $tariff */
    private $tariff;
    /** @var  array $floorIds */
    private $floorIds;
    /** @var  ContainerInterface $container */
    private $container;
    /** @var DataCollectorTranslator $translator */
    private $translator;
    /** @var $accommodationManipulator PackageAccommodationManipulator */
    private $accommodationManipulator;
    private $pageNumber;
    private $clientConfig;

    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isRoomsByRoomTypeIdsInit = false;
    private $roomsByRoomTypeIds = [];
    private $isPackageAccommodationsDataInit = false;
    private $packageAccommodationsData = [];
    private $isAvailableRoomTypesInit = false;
    private $availableRoomTypes;

    /**
     * @param DocumentManager $dm
     * @param Helper $helper
     * @param PackageAccommodationManipulator $accommodationManipulator
     * @param TranslatorInterface $translator
     * @param ContainerInterface $container
     */
    public function __construct(
        DocumentManager $dm,
        Helper $helper,
        PackageAccommodationManipulator $accommodationManipulator,
        TranslatorInterface $translator,
        $container
    ) {
        $this->dm = $dm;
        $this->container = $container;
        $this->helper = $helper;
        $this->accommodationManipulator = $accommodationManipulator;
        $this->translator = $translator;
        $this->clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
    }

    /**
     * @param Hotel $hotel
     * @param \DateTime $beginDate
     * @param \DateTime $endDate
     * @param int[] $roomTypeIds
     * @param array $housingIds
     * @param array $floorIds
     * @param Tariff $tariff
     * @param $pageNumber
     * @return ChessBoardDataBuilder
     */
    public function init(
        Hotel $hotel,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        array $housingIds = [],
        array $floorIds = [],
        Tariff $tariff = null,
        $pageNumber
    ) {
        $this->hotel = $hotel;
        $this->roomTypeIds = $roomTypeIds;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->housingIds = $housingIds;
        $this->tariff = $tariff;
        $this->floorIds = $floorIds;
        $this->pageNumber = $pageNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getAccommodationData()
    {
        $accommodationData = [];

        foreach ($this->getAccommodationIntervals() as $interval) {
            $accommodationData[] = $interval->__toArray();
        }

        return $this->getAccommodationIntervals();
    }

    /**
     * @return array
     */
    public function getNoAccommodationPackageIntervals()
    {
        $noAccommodationIntervals = [];
        foreach ($this->getPackagesWithoutAccommodation() as $package) {
            /** @var Package $package */
            $intervalData = $this->container->get('mbh.chess_board_unit')->setInitData($package);
            $noAccommodationIntervals[$intervalData->getId()] = $intervalData;
        }

        return array_merge($noAccommodationIntervals, $this->getDateIntervalsWithoutAccommodation());
    }

    /**
     * Получение массива данныых о количестве свободных комнат, разделенных по дням
     *
     * @return array
     */
    public function getDayNoAccommodationPackageCounts()
    {
        $counts = [];
        $roomTypes = $this->getRoomTypes();
        $daysArray = $this->getDaysArray();

        foreach ($roomTypes as $roomType) {
            foreach ($daysArray as $day) {
                $counts[$roomType->getId()][$day->format('d.m.Y')] = 0;
            }
        }

        foreach ($this->getNoAccommodationPackageIntervals() as $interval) {
            /** @var ChessBoardUnit $interval */
            $minDate = max($this->beginDate, $interval->getBeginDate());
            $maxDate = min($this->endDate, $interval->getEndDate());

            foreach (new \DatePeriod($minDate, new \DateInterval('P1D'), $maxDate) as $day) {
                /** @var \DateTime $day */
                $counts[$interval->getRoomTypeId()][$day->format('d.m.Y')]++;
            }
        }

        foreach ($counts as $roomTypeId => $roomTypeCounts) {
            $counts[$roomTypeId] = array_values($roomTypeCounts);
        }

        return $counts;
    }

    /**
     * @return Package[]
     */
    public function getPackagesWithoutAccommodation()
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->hotel = $this->hotel;
        //$packageQueryCriteria->confirmed
        $packageQueryCriteria->filter = 'live_between';
        $packageQueryCriteria->liveBegin = $this->beginDate;
        $packageQueryCriteria->setIsWithoutAccommodation(true);
        $packageQueryCriteria->liveEnd = $this->endDate;
        foreach ($this->getRoomTypeIds() as $roomTypeId) {
            $packageQueryCriteria->addRoomTypeCriteria($roomTypeId);
        }
        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->findByQueryCriteria($packageQueryCriteria);

        $this->loadRelatedToPackagesData($packages);

        return $packages;
    }

    /**
     * Возвращает данные о периодах без размещения броней, имеющих неполное размещение,
     * ...то есть имеющих данные о размещении, но дата окончания последнего размещения меньше даты выезда брони
     *
     * @return array
     */
    private function getDateIntervalsWithoutAccommodation()
    {
        $dateIntervalsWithoutAccommodation = [];
        $packages = [];
        foreach ($this->getPackageAccommodationsData() as $packageAccommodationData) {
            /** @var Package $package */
            $package = $packageAccommodationData['package'];
            $packages[$package->getId()] = $package;
        }

        foreach ($packages as $package) {
            $emptyIntervals = $this->accommodationManipulator->getEmptyIntervals($package);
            foreach ($emptyIntervals as $emptyInterval) {
                $intervalData = $this->container
                    ->get('mbh.chess_board_unit')->setInitData($package, null, $emptyInterval);
                $dateIntervalsWithoutAccommodation[$intervalData->getId()] = $intervalData;
            }
        }

        return $dateIntervalsWithoutAccommodation;
    }

    /**
     * @return array
     */
    public function getAccommodationIntervals()
    {
        $accommodationIntervals = [];

        foreach ($this->getPackageAccommodationsData() as $packageAccommodationData) {
            $package = $packageAccommodationData['package'];
            $accommodation = $packageAccommodationData['accommodation'];
            $intervalData = $this->container
                ->get('mbh.chess_board_unit')
                ->setInitData(
                    $package,
                    $accommodation,
                    [],
                    $packageAccommodationData['Early check-in'],
                    $packageAccommodationData['Late check-out']
                );
            $accommodationIntervals[$intervalData->getId()] = $intervalData;
        }


        return $accommodationIntervals;
    }

    /**
     * @return array ['accommodation', 'package']
     */
    private function getPackageAccommodationsData()
    {
        if (!$this->isPackageAccommodationsDataInit) {

            $rooms = [];
            foreach ($this->getRoomsByRoomTypeIds() as $roomsByRoomTypeId) {
                $rooms = array_merge($rooms, $roomsByRoomTypeId);
            }

            if (count($rooms) > 0) {
                $accommodations = $this->dm->getRepository('MBHPackageBundle:PackageAccommodation')
                    ->fetchWithAccommodation(
                        $this->beginDate, $this->endDate, $this->helper->toIds($rooms), null, false
                    )->toArray();
                $accommodationsIds = array_map(function (PackageAccommodation $accommodation) {
                    return $accommodation->getId();
                }, $accommodations);

                /** @var Package[] $packages */
                $packages = $this->dm
                    ->getRepository('MBHPackageBundle:Package')
                    ->createQueryBuilder()
                    ->field('accommodations.id')->in($accommodationsIds)
                    ->getQuery()
                    ->execute();

                $packagesIds = [];
                $packagesByAccommodationIds = [];
                foreach ($packages as $package) {
                    $packagesIds[$package->getId()] = $package->getId();
                    foreach ($package->getAccommodations() as $accommodation) {
                        $packagesByAccommodationIds[$accommodation->getId()] = $package;
                    }
                }

                $this->loadRelatedToPackagesData($packages);
                $packageServicesByPackageIdAndCode = $this->getPackageServicesByPackageIdAndCode($packagesIds);

                $packageAccommodationsData = [];
                /** @var PackageAccommodation $accommodation */
                foreach ($accommodations as $accommodation) {
                    if (!isset($packagesByAccommodationIds[$accommodation->getId()])) {
                        $this->container->get('logger')->error('FOR ACCOMMODATION ' . $accommodation->getId() . ' NOT FOUND PACKAGE');
                    } else {
                        $package = $packagesByAccommodationIds[$accommodation->getId()];

                        $data = [
                            'package' => $package,
                            'accommodation' => $accommodation,
                        ];
                        $data['Early check-in'] = isset($packageServicesByPackageIdAndCode[$package->getId()]['Early check-in']);
                        $data['Late check-out'] = isset($packageServicesByPackageIdAndCode[$package->getId()]['Late check-out']);

                        $packageAccommodationsData[] = $data;
                    }
                }

                usort($packageAccommodationsData, function ($a, $b) {
                    /** @var Package $aPackage */
                    $aPackage = $a['package'];
                    /** @var Package $bPackage */
                    $bPackage = $b['package'];
                    /** @var PackageAccommodation $aAccommodation */
                    $aAccommodation = $a['accommodation'];
                    /** @var PackageAccommodation $bAccommodation */
                    $bAccommodation = $b['accommodation'];

                    $idComparisonResult = strcmp($aPackage->getId(), $bPackage->getId());
                    if ($idComparisonResult < 1) {
                        return $idComparisonResult;
                    }

                    return $aAccommodation->getBegin() > $bAccommodation->getBegin() ? -1 : 1;
                });

                $this->packageAccommodationsData = $packageAccommodationsData;
            }

            $this->isPackageAccommodationsDataInit = true;
        }

        return $this->packageAccommodationsData;
    }

    private function getPackageServicesByPackageIdAndCode($packagesIds)
    {
        $lateCheckInAndLateCheckoutServices = $this->dm
            ->getRepository('MBHPriceBundle:Service')
            ->createQueryBuilder()
            ->field('code')->in(['Early check-in', 'Late check-out'])
            ->getQuery()
            ->execute();

        $lateCheckInAndLateCheckoutServicesIds = array_map(function (Service $service) {
            return $service->getId();
        }, $lateCheckInAndLateCheckoutServices->toArray());

        /** @var PackageService[] $packageServices */
        $packageServices = $this->dm
            ->getRepository('MBHPackageBundle:PackageService')
            ->createQueryBuilder()
            ->field('package.id')->in($packagesIds)
            ->field('service.id')->in($lateCheckInAndLateCheckoutServicesIds)
            ->getQuery()
            ->execute()
            ->toArray();

        $packageServicesByPackageIdAndCode = [];
        foreach ($packageServices as $packageService) {
            $packageServicesByPackageIdAndCode[$packageService->getPackage()->getId()][$packageService->getService()->getCode()] = $packageService;
        }

        return $packageServicesByPackageIdAndCode;
    }

    /**
     * @return array [roomTypeId => date string(d.m.Y) => left rooms count]
     */
    public function getLeftRoomCounts()
    {
        $roomCacheData = [];

        /** @var array [roomTypeId => [tariffId => [date string(d.m.Y) => RoomCache]]] $roomCaches */
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch($this->beginDate,
                $this->endDate,
                $this->hotel,
                $this->getRoomTypeIds(),
                $this->tariff === null ? [] : [$this->tariff],
                true
            );

        $endDate = (clone $this->endDate)->add(new \DateInterval('P1D'));

        foreach ($this->getRoomTypes() as $roomType) {

            if (isset($roomCaches[$roomType->getId()])) {
                $roomCachesByDates = isset($roomCaches[$roomType->getId()][0]) ? $roomCaches[$roomType->getId()][0] : current($roomCaches[$roomType->getId()]);
                foreach (new \DatePeriod($this->beginDate, new \DateInterval('P1D'), $endDate) as $day) {
                    /** @var \DateTime $day */
                    if (isset($roomCachesByDates[$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCachesByDates[$day->format('d.m.Y')];
                        $roomCacheData[$roomType->getId()][] = $currentDateRoomCache->getLeftRooms();
                    } else {
                        $roomCacheData[$roomType->getId()][] = '';
                    }
                }
            }
        }

        return $roomCacheData;
    }

    /**
     * @return array
     */
    public function getCalendarData()
    {
        $calendarData = [];

        foreach ($this->getDaysArray() as $day) {
            /** @var \DateTime $day */
            $monthIndex = $day->format('m.Y');

            if (isset($calendarData[$monthIndex])) {
                $calendarData[$monthIndex]['daysCount']++;
            } else {
                $calendarData[$monthIndex] = [
                    'month' => $day->format('n'),
                    'year' => $day->format('Y'),
                    'daysCount' => 1
                ];
            }
        }

        return $calendarData;
    }

    /**
     * @return array
     */
    public function getDaysArray()
    {
        $days = [];
        $endDate = (clone $this->endDate)->modify('1 day');
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($this->beginDate, $interval, $endDate);
        foreach ($period as $day) {
            $days[] = $day;
        }

        return $days;
    }

    /**
     * @return array
     */
    public function getRoomTypeData()
    {
        $roomTypeData = [];
        /** @var array [roomTypeId => RoomType] $roomsByRoomTypeIds */
        $roomsByRoomTypeIds = $this->getRoomsByRoomTypeIds();

        foreach ($this->getRoomTypes() as $roomType) {

            /** @var RoomType $roomType */
            $roomTypeData[$roomType->getId()] = [
                'isEnabled' => $roomType->getIsEnabled(),
                'name' => $roomType->getName(),
                'rooms' => $this->getRoomsData($roomsByRoomTypeIds, $roomType)
            ];
        }

        return $roomTypeData;
    }

    /**
     * @return array
     */
    public function getRoomsByRoomTypeIds()
    {
        $username = $this->container->get('security.token_storage')->getToken()->getUsername();
        $numberOfRooms = $this->clientConfig->getFrontSettings()->getRoomsInChessboard($username);
        if (!$this->isRoomsByRoomTypeIdsInit) {
            $roomTypes = $this->getRoomTypeIds();
            $skipValue = ($this->pageNumber - 1) * $numberOfRooms;

            $allRooms = $this->dm->getRepository('MBHHotelBundle:Room')
                ->fetch($this->hotel, $roomTypes, $this->housingIds, $this->floorIds, null,
                    null, false, true, ['fullTitle' => 'asc'])->toArray();

            usort($allRooms, function(Room $room1, Room $room2) {
                if ($room1->getRoomType() !== $room2->getRoomType()) {
                    return $room1->getRoomType()->getName() > $room2->getRoomType()->getName() ? 1 : -1;
                }

                $firstRoomIntName = $this->helper->getFirstNumberFromString($room1->getName());
                $secondRoomIntName = $this->helper->getFirstNumberFromString($room2->getName());

                if (!$firstRoomIntName && is_numeric($secondRoomIntName)) {
                    return 1;
                } elseif (is_numeric($firstRoomIntName) && !$secondRoomIntName) {
                    return -1;
                } elseif (!$firstRoomIntName && !$secondRoomIntName) {
                    return $room1->getName() > $room2->getName() ? 1 : -1;
                }

                return $firstRoomIntName > $secondRoomIntName ? 1 : -1;
            });

            $roomsByRoomTypeIds = [];
            /** @var Room $room */
            foreach ($allRooms as $index => $room) {
                $numberOfRoom = $index + 1;
                if ($numberOfRooms !== 0 && $numberOfRoom > $skipValue + $numberOfRooms) {
                    break;
                }
                if ($skipValue < $numberOfRoom) {
                    isset($roomsByRoomTypeIds[$room->getRoomType()->getId()])
                        ? $roomsByRoomTypeIds[$room->getRoomType()->getId()][] = $room
                        : $roomsByRoomTypeIds[$room->getRoomType()->getId()] = [$room];
                }
            }

            $this->roomsByRoomTypeIds = $roomsByRoomTypeIds;
            $this->isRoomsByRoomTypeIdsInit = true;
        }

        return $this->roomsByRoomTypeIds;
    }

    /**
     * @return int
     */
    public function getRoomCount()
    {
        $roomTypes = $this->getRoomTypeIds();

        return $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery($this->hotel, $roomTypes, $this->housingIds, $this->floorIds, null, null, true)
            ->getQuery()
            ->count();
    }

    /**
     * @return array
     */
    private function getRoomTypeIds()
    {
        if (count($this->roomTypeIds) > 0) {
            return array_intersect($this->roomTypeIds, $this->getAvailableRoomTypeIds());
        }

        return $this->getAvailableRoomTypeIds();
    }

    /**
     * @param $roomsByRoomTypeIds
     * @param RoomType $roomType
     * @return array [roomTypeId => ['name', 'housing', 'floor']]
     */
    private function getRoomsData($roomsByRoomTypeIds, RoomType $roomType)
    {
        $roomsData = [];

        if (isset($roomsByRoomTypeIds[$roomType->getId()])) {
            $roomsByRoomType = $roomsByRoomTypeIds[$roomType->getId()];

            foreach ($roomsByRoomType as $room) {
                /** @var Room $room */
                $roomsData[$room->getId()] = [
                    'name' => $room->getName(),
                    'statuses' => $room->getStatus()->toArray(),
                    'room' => $room
                ];
            }
        }

        return $roomsData;
    }

    /**
     * Lazy loading of available room types
     *
     * @return RoomType[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getAvailableRoomTypes()
    {
        if (!$this->isAvailableRoomTypesInit) {
            $isDisableableOn = $this->container->get('mbh.client_config_manager')->fetchConfig()->isDisableableOn();
            $filterCollection = $this->dm->getFilterCollection();
            if ($isDisableableOn && !$filterCollection->isEnabled('disableable')) {
                $filterCollection->enable('disableable');
            }

            $this->availableRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($this->hotel)->toArray();

            if ($isDisableableOn && $filterCollection->isEnabled('disableable')) {
                $filterCollection->disable('disableable');
            }
            $this->isAvailableRoomTypesInit = true;
        }

        return $this->availableRoomTypes;
    }

    /**
     * @return array
     */
    private function getAvailableRoomTypeIds()
    {
        $roomTypeIds = [];
        /** @var RoomType $roomType */
        foreach ($this->getAvailableRoomTypes() as $roomType) {
            $roomTypeIds[] = $roomType->getId();
        }

        return $roomTypeIds;
    }

    /**
     * Ленивая загрузка массива объектов RoomType, используемых в данном запросе
     * @return RoomType[]
     */
    private function getRoomTypes()
    {
        if (!$this->isRoomTypesInit) {

            $this->roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($this->hotel, $this->getRoomTypeIds())
                ->toArray();

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }

    /**
     * @param Package[] $packages
     */
    private function loadRelatedToPackagesData($packages)
    {
        $orderIds = [];
        $touristIds = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            /** @var PersistentCollection $tourists */
            $tourists = $package->getTourists();
            $ids = [];
            foreach ($tourists->getMongoData() as $touristData) {
                $ids[] = $touristData['$id'];
            }
            $touristIds = array_merge($touristIds, $ids);
            $orderIds[] = $package->getOrder()->getId();
        }

        if (count($touristIds) > 0) {
            $tourists = $this->dm
                ->getRepository('MBHPackageBundle:Tourist')
                ->createQueryBuilder()
                ->field('id')->in($touristIds)
                ->getQuery()
                ->execute()
                ->toArray();
        }

        if (count($orderIds) > 0) {
            $orders = $this->dm
                ->getRepository('MBHPackageBundle:Order')
                ->createQueryBuilder()
                ->field('id')->in($orderIds)
                ->getQuery()
                ->execute()
                ->toArray();
        }
    }
}