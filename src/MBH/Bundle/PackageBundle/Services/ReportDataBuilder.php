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
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;

class ReportDataBuilder
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
    /** @var  Housing $housing */
    private $housing;
    /** @var  Tariff $tariff */
    private $tariff;
    /** @var  int $floor */
    private $floor;

    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isRoomCachesInit = false;
    private $roomCaches = [];

    /**
     * ReportDataBuilder constructor.
     * @param DocumentManager $dm
     * @param Helper $helper
     */
    public function __construct(DocumentManager $dm, Helper $helper)
    {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /**
     * @param Hotel $hotel
     * @param int[] $roomTypeIds
     * @param \DateTime $beginDate
     * @param \DateTime $endDate
     * @param Housing $housing
     * @param Tariff $tariff
     * @param $floor
     * @return ReportDataBuilder
     */
    public function init(Hotel $hotel,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        Housing $housing = null,
        $floor = null,
        Tariff $tariff = null)
    {
        $this->hotel = $hotel;
        $this->roomTypeIds = $roomTypeIds;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->housing = $housing;
        $this->tariff = $tariff;
        $this->floor = $floor;

        return $this;
    }

    public function getPackageData()
    {
        $packagesData = [];

        $packages = $this->getPackages()->toArray();


        foreach ($packages as $package) {
            /** @var Package $package */
            //TODO: Дополнить необходимыми данными
            $packagesData[] = [
                //TODO: Плательщик не всегда есть. Что вместо него?
                'payer' => $package->getPayer() ? $package->getPayer() : $package->getName(),
                'price' => $package->getPrice(),
                'begin' => $package->getBegin(),
                'end' => $package->getEnd(),

            ];
        }
        return $packagesData;
    }

    public function getPackages()
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->hotel = $this->hotel;
        //$packageQueryCriteria->confirmed
        $packageQueryCriteria->filter = 'live_between';
        $packageQueryCriteria->liveBegin = $this->beginDate;
        $packageQueryCriteria->liveEnd = $this->endDate;

        if ($this->housing !== null || $this->floor !== null) {
            $rooms = $this->getRooms();
            $roomIds = $this->helper->toIds($rooms);
            return $this->dm->getRepository('MBHPackageBundle:Package')
                ->fetchWithAccommodation($this->beginDate, $this->endDate, $roomIds, null, false);
        } else {
            return $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($packageQueryCriteria);
        }
    }

    /**
     * @return array [roomTypeId => date string(d.m.Y) => left rooms count]
     */
    public function getRoomCacheData()
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

        foreach ($this->getRoomTypes() as $roomType) {

            if (isset($roomCaches[$roomType->getId()])) {
                //Данные о комнатах могут быть получены либо для всех тарифов, и массив, содержащий их будет иметь индекс 0,
                // либо для одного и будет иметь индекс тарифа, для которого искали данные
                $roomCachesByDates = current($roomCaches[$roomType->getId()]);
                foreach ($roomCachesByDates as $dateString => $roomCacheByDate) {

                    /** @var RoomCache $roomCacheByDate */
                    $roomCacheData[$roomType->getId()][$dateString] = $roomCacheByDate->getLeftRooms();
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
        $rooMTypeData = [];
        /** @var array [roomTypeId => RoomType] $roomsByRoomTypeIds */
        $roomsByRoomTypeIds = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetch($this->hotel, $this->roomTypeIds, $this->housing, $this->floor, null, null, true);

        foreach ($this->getRoomTypes() as $roomType) {

            /** @var RoomType $roomType */
            $rooMTypeData[$roomType->getId()] = [
                'name' => $roomType->getName(),
                'rooms' =>$this->getRoomsData($roomsByRoomTypeIds, $roomType)
            ];
        }

        return $rooMTypeData;
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
                $houseDetails = '';
                if ($room->getHousing()) {
                    $houseDetails .= 'Корпус "' . $room->getHousing()->getName() . '"<br>' ;
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
     * Ленивая загрузка массива объектов Room
     * @return array
     */
    private function getRooms()
    {
        if (!$this->isRoomCachesInit) {

            $this->roomCaches = $this->dm->getRepository('MBHHotelBundle:Room')
                ->fetch($this->hotel, $this->roomTypeIds, $this->housing, $this->floor);

            $this->isRoomCachesInit = true;
        }

        return $this->roomCaches;
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