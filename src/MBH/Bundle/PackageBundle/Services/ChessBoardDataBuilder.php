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
    /** @var array $hotelIds */
    private $hotelIds;
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

    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isRoomsByRoomTypeIdsInit = false;
    private $roomsByRoomTypeIds = [];
    private $isPackageAccommodationsDataInit = false;
    private $packageAccommodationsData = [];
    private $isAvailableRoomTypesInit = false;
    private $availableRoomTypes;
    private $roomTypeIdsInSelectedHotels;
    private $isRoomTypeIdsInSelectedHotelsInit = false;

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
    }

    /**
     * @param array $hotelIds
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
        array $hotelIds,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        array $housingIds = [],
        array $floorIds = [],
        Tariff $tariff = null,
        $pageNumber
    ) {
        $this->hotelIds = $hotelIds;
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
     * @throws \Exception
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getPackagesWithoutAccommodation()
    {
        if (!empty($this->getRoomTypeIds())) {
            $packageQueryCriteria = new PackageQueryCriteria();
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
        } else {
            $packages = [];
        }

        return $packages;
    }

    /**
     * Возвращает данные о периодах без размещения броней, имеющих неполное размещение,
     * ...то есть имеющих данные о размещении, но дата окончания последнего размещения меньше даты выезда брони
     *
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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

    /**
     * @param $packagesIds
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
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
     * @throws \Exception
     */
    public function getLeftRoomCounts()
    {
        $roomCacheData = [];

        /** @var array [roomTypeId => [tariffId => [date string(d.m.Y) => RoomCache]]] $roomCaches */
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch($this->beginDate,
                $this->endDate,
                null,
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRoomsByRoomTypeIds()
    {
        $username = $this->container->get('security.token_storage')->getToken()->getUsername();
        $numberOfRooms = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig()
            ->getFrontSettings()->getRoomsInChessboard($username);
        if (!$this->isRoomsByRoomTypeIdsInit) {
            $roomTypes = $this->getRoomTypeIds();
            $skipValue = ($this->pageNumber - 1) * $numberOfRooms;

            $allRooms = $this->dm->getRepository('MBHHotelBundle:Room')
                ->fetch(null, $roomTypes, $this->housingIds, $this->floorIds, null,
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRoomCount()
    {
        $roomTypes = $this->getRoomTypeIds();

        return $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetchQuery(null, $roomTypes, $this->housingIds, $this->floorIds, null, null, true)
            ->getQuery()
            ->count();
    }

    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRoomTypeIds()
    {
        $roomTypeIdsInSelectedHotels = $this->getRoomTypeIdsInSelectedHotels();
        $roomTypeIds = $this->getAvailableRoomTypeIds();
        if (count($this->roomTypeIds) > 0) {
            $roomTypeIds = array_intersect($this->roomTypeIds, $roomTypeIds);
        }
        if (!empty($roomTypeIdsInSelectedHotels)) {
            $roomTypeIds = array_intersect($roomTypeIdsInSelectedHotels, $roomTypeIds);
        }

        return $roomTypeIds;
    }

    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRoomTypeIdsInSelectedHotels() {
        if (!$this->isRoomTypeIdsInSelectedHotelsInit) {
            $hotels = $this->dm
                ->getRepository('MBHHotelBundle:Hotel')
                ->getByIds($this->hotelIds, false)
                ->toArray();
            $this->roomTypeIdsInSelectedHotels = [];
            array_walk($hotels, function(Hotel $hotel) {
                $this->roomTypeIdsInSelectedHotels = array_merge(array_map(function(RoomType $roomType) {
                    return $roomType->getId();
                }, $hotel->getRoomTypes()->toArray()), $this->roomTypeIdsInSelectedHotels);
            });

            $this->isRoomTypeIdsInSelectedHotelsInit = true;
        }

        return $this->roomTypeIdsInSelectedHotels;
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
     */
    public function getAvailableRoomTypes()
    {
        if (!$this->isAvailableRoomTypesInit) {
            $isDisableableOn = $this->container->get('mbh.client_config_manager')->fetchConfig()->isDisableableOn();
            $filterCollection = $this->dm->getFilterCollection();
            if ($isDisableableOn && !$filterCollection->isEnabled('disableable')) {
                $filterCollection->enable('disableable');
            }

            $this->availableRoomTypes = $this->dm
                ->getRepository('MBHHotelBundle:RoomType')
                ->findAll();

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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getRoomTypes()
    {
        if (!$this->isRoomTypesInit) {

            if (!empty($this->getRoomTypeIds())) {
                $this->roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                    ->fetch(null, $this->getRoomTypeIds())
                    ->toArray();
            } else {
                $this->roomTypes = [];
            }

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }

    /**
     * @param Package[] $packages
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
