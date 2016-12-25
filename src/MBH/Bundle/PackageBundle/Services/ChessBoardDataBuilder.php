<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Housing;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Models\ChessBoard\ChessBoardUnit;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    /** @var DataCollectorTranslator $translator */
    private $translator;

    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isRoomsByRoomTypeIdsInit = false;
    private $roomsByRoomTypeIds = [];
    private $isPackageAccommodationsInit = false;
    private $packageAccommodations = [];

    /**
     * @param DocumentManager $dm
     * @param Helper $helper
     * @param ContainerInterface $container
     */
    public function __construct(DocumentManager $dm, Helper $helper, $container)
    {
        $this->dm = $dm;
        $this->helper = $helper;
        $this->translator = $container->get('translator');
    }

    /**
     * @param Hotel $hotel
     * @param \DateTime $beginDate
     * @param \DateTime $endDate
     * @param int[] $roomTypeIds
     * @param array $housingIds
     * @param array $floorIds
     * @param Tariff $tariff
     * @return ChessBoardDataBuilder
     */
    public function init(
        Hotel $hotel,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        ?array $housingIds = [],
        ?array $floorIds = [],
        Tariff $tariff = null
    ) {
        $this->hotel = $hotel;
        $this->roomTypeIds = $roomTypeIds;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->housingIds = $housingIds;
        $this->tariff = $tariff;
        $this->floorIds = $floorIds;

        return $this;
    }

    public function getAccommodationData()
    {
        $accommodationData = [];

        foreach ($this->getAccommodationIntervals() as $interval) {
            $accommodationData[] = $interval->__toArray();
        }

        return $this->getAccommodationIntervals();
    }

    public function getNoAccommodationIntervals()
    {
        $noAccommodationIntervals = [];
        foreach ($this->getPackagesWithoutAccommodation() as $package) {
            /** @var Package $package */
            $noAccommodationIntervals[] = new ChessBoardUnit(
                $package->getId(),
                $package->getBegin(),
                $package->getEnd(),
                $this->getIntervalName($package),
                $package->getRoomType()->getId(),
                $package->getPaidStatus(),
                $package->getPrice(),
                $package->getBegin(),
                $package->getEnd(),
                $package->getIsCheckIn(),
                $package->getIsCheckOut(),
                $package->getIsLocked()
            );
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

        foreach ($this->getNoAccommodationIntervals() as $interval) {
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

    public function getPackagesWithoutAccommodation()
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->hotel = $this->hotel;
        //$packageQueryCriteria->confirmed
        $packageQueryCriteria->filter = 'live_between';
        $packageQueryCriteria->liveBegin = $this->beginDate;
        $packageQueryCriteria->setIsWithoutAccommodation(true);
        $packageQueryCriteria->liveEnd = $this->endDate;
        if (count($this->roomTypeIds) > 0) {
            foreach ($this->roomTypeIds as $roomTypeId) {
                $packageQueryCriteria->addRoomTypeCriteria($roomTypeId);
            }
        }

        return $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($packageQueryCriteria);
    }

    /**
     * Возвращает данные о периодах без размещения броней, имеющих неполное размещение, то есть имеющих данные о размещении, но
     * ... дата окончания последнего размещения меньше даты выезда брони
     *
     * @return array
     */
    private function getDateIntervalsWithoutAccommodation()
    {
        $dateIntervalsWithoutAccommodation = [];
        foreach ($this->getPackageAccommodations() as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            $package = $accommodation->getPackage();
            /** @var PackageAccommodation $packageLastAccommodation */
            $accommodations = $package->getAccommodations()->toArray();
            $packageLastAccommodation = end($accommodations);
            $packageLastAccommodationDate = $packageLastAccommodation->getEnd();

            if ($package->getEnd()->format('d.m.Y') != $packageLastAccommodationDate->format('d.m.Y')) {
                $dateIntervalsWithoutAccommodation[] = new ChessBoardUnit(
                    $package->getId(),
                    $packageLastAccommodationDate,
                    $package->getEnd(),
                    $this->getIntervalName($package),
                    $package->getRoomType()->getId(),
                    $package->getPaidStatus(),
                    $package->getPrice(),
                    $package->getBegin(),
                    $package->getEnd(),
                    $package->getIsCheckIn(),
                    $package->getIsCheckOut(),
                    $package->getIsLocked()
                );
            }
        }

        return $dateIntervalsWithoutAccommodation;
    }

    public function getAccommodationIntervals()
    {
        $accommodationIntervals = [];
        foreach ($this->getPackageAccommodations() as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            $package = $accommodation->getPackage();

            $accommodationIntervals[] = (new ChessBoardUnit(
                $accommodation->getId(),
                $accommodation->getBegin(),
                $accommodation->getEnd(),
                $package->getNumberWithPrefix(),
                $accommodation->getAccommodation()->getRoomType()->getId(),
                $package->getPaidStatus(),
                $package->getPrice(),
                $package->getBegin(),
                $package->getEnd(),
                $package->getIsCheckIn(),
                $package->getIsCheckOut(),
                $package->getIsLocked(),
                $package->getPayer(),
                $accommodation->getAccommodation()->getId(),
                $this->getAccommodationRelativePosition($accommodation, $package)
            ))
                ->setPackageId($package->getId())
            ;
        }

        return $accommodationIntervals;
    }

    /**
     * Получение относительного положения размещения по отношению к остальным размещениям брони
     * Размещение может занимать полное время брони, быть первым размещением, последним размещением или промежуточным
     *
     * @param PackageAccommodation $accommodation
     * @param Package $package
     * @return string
     */
    private function getAccommodationRelativePosition(PackageAccommodation $accommodation, Package $package)
    {
        $packageBeginString = $package->getBegin()->format('d.m.Y');
        $lastPackageAccommodationEndString = $package->getLastEndAccommodation()->format('d.m.Y');
        $accommodationBeginString = $accommodation->getBegin()->format('d.m.Y');
        $accommodationEndString = $accommodation->getEnd()->format('d.m.Y');

        if ($accommodationBeginString == $packageBeginString
            && $accommodationEndString == $lastPackageAccommodationEndString
        ) {
            return ChessBoardUnit::FULL_PACKAGE_ACCOMMODATION;
        }
        if ($accommodationBeginString == $packageBeginString
            && $accommodationEndString != $lastPackageAccommodationEndString
        ) {
            return ChessBoardUnit::LEFT_RELATIVE_POSITION;
        }
        if ($accommodationEndString == $lastPackageAccommodationEndString
            && $accommodationBeginString != $packageBeginString
        ) {
            return ChessBoardUnit::RIGHT_RELATIVE_POSITION;
        }

        return ChessBoardUnit::MIDDLE_RELATIVE_POSITION;
    }

    /**
     * Возвращает строку, указываемую на блоках шахматки
     *
     * @param Package $package
     * @return mixed
     */
    private function getIntervalName($package)
    {
        return $package->getPayer() ? $package->getPayer()->getName() : $package->getName();
    }

    private function getPackageAccommodations()
    {
        if (!$this->isPackageAccommodationsInit) {

            $rooms = [];
            foreach ($this->getRoomsByRoomTypeIds() as $roomsByRoomTypeId) {
                $rooms = array_merge($rooms, $roomsByRoomTypeId);
            }

            $this->packageAccommodations = $this->dm->getRepository('MBHPackageBundle:Package')->fetchWithAccommodation(
                $this->beginDate, $this->endDate, $this->helper->toIds($rooms), null, false
            );

            $this->isPackageAccommodationsInit = true;
        }

        return $this->packageAccommodations;
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
                $this->roomTypeIds,
                $this->tariff === null ? [] : [$this->tariff],
                true
            );

        $endDate = (clone $this->endDate)->add(new \DateInterval('P1D'));

        foreach ($this->getRoomTypes() as $roomType) {

            if (isset($roomCaches[$roomType->getId()])) {
                //Данные о комнатах могут быть получены либо для всех тарифов, и массив, содержащий их будет иметь индекс 0,
                // либо для одного и будет иметь индекс тарифа, для которого искали данные
                $roomCachesByDates = current($roomCaches[$roomType->getId()]);
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

    public function getRoomTypeData()
    {
        $roomTypeData = [];
        /** @var array [roomTypeId => RoomType] $roomsByRoomTypeIds */
        $roomsByRoomTypeIds = $this->getRoomsByRoomTypeIds();

        foreach ($this->getRoomTypes() as $roomType) {

            /** @var RoomType $roomType */
            $roomTypeData[$roomType->getId()] = [
                'name' => $roomType->getName(),
                'rooms' => $this->getRoomsData($roomsByRoomTypeIds, $roomType)
            ];
        }

        return $roomTypeData;
    }


    public function getRoomsByRoomTypeIds()
    {
        if (!$this->isRoomsByRoomTypeIdsInit) {

            $roomTypes = count($this->roomTypeIds) > 0 ? $this->roomTypeIds : null;

            $this->roomsByRoomTypeIds = $this->dm->getRepository('MBHHotelBundle:Room')
                ->fetch($this->hotel, $roomTypes, $this->housingIds, $this->floorIds, null, null, true);

            $this->isRoomsByRoomTypeIdsInit = true;
        }

        return $this->roomsByRoomTypeIds;
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
                //TODO: Переделать
                $houseDetails = '';
                if ($room->getHousing()) {
                    $houseDetails .= "Корпус \"" . $room->getHousing()->getName() . "\"<br>";
                }
                if ($room->getFloor()) {
                    $houseDetails .= 'Этаж ' . $room->getFloor();
                }

                $roomsData[$room->getId()] = [
                    'name' => $room->getName(),
                    'statuses' => $room->getStatus()->toArray(),
                    'houseDetails' => $houseDetails
                ];
            }
        }

        return $roomsData;
    }


    /**
     * Ленивая загрузка массива объектов RoomType, используемых в данном запросе
     * @return RoomType[]
     */
    private function getRoomTypes()
    {
        if (!$this->isRoomTypesInit) {

            $this->roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($this->hotel, $this->roomTypeIds)->toArray();

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }


}