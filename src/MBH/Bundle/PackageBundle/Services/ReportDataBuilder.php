<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Housing;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /**
     * ReportDataBuilder constructor.
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
     * @return ReportDataBuilder
     */
    public function init(Hotel $hotel,
        \DateTime $beginDate,
        \DateTime $endDate,
        $roomTypeIds = [],
        array $housingIds = [],
        array $floorIds = [],
        Tariff $tariff = null)
    {
        $this->hotel = $hotel;
        $this->roomTypeIds = $roomTypeIds;
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        $this->housingIds = $housingIds;
        $this->tariff = $tariff;
        $this->floorIds = $floorIds;

        return $this;
    }

    public function getPackageData()
    {
        $packagesData = [];

        $packages = $this->getPackages()->toArray();

        foreach ($packages as $package) {
            /** @var Package $package */
            // Id строки таблицы шахматки, в которую будет помещаться бронь
            //Если в брони указаны данные о размещении, указываем соответствующий Id комнаты
            if ($package->getAccommodation()) {
                $reportTableLineId = $package->getAccommodation()->getId();
            //В противном случае указываем Id строки "Без номера" для указанного типа комнаты
            } else {
                $reportTableLineId = 'no_accommodation' . $package->getRoomType()->getId();
            }

            //TODO: Дополнить необходимыми данными
            $packagesData[] = [
                //TODO: Плательщик не всегда есть. Что вместо него?
                'id' => $package->getId(),
                'payer' => $package->getPayer() ? $package->getPayer()->getName() : $package->getName(),
                'price' => $package->getPrice(),
                'begin' => $package->getBegin(),
                'end' => $package->getEnd(),
                'roomTypeId' => $package->getRoomType()->getId(),
                'accommodation' => $reportTableLineId,
            ];
        }

        return $packagesData;
    }

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

        foreach ($this->getPackages() as $package) {
            if (!$package->getAccommodation()) {
                $minDate = max($this->beginDate, $package->getBegin());
                $maxDate = min($this->endDate, $package->getEnd());

                foreach (new \DatePeriod($minDate, new \DateInterval('P1D'), $maxDate) as $day) {
                    /** @var \DateTime $day */
                    $counts[$package->getRoomType()->getId()][$day->format('d.m.Y')]++;
                }
            }
        }

        foreach ($counts as $roomTypeId => $roomTypeCounts) {
            $counts[$roomTypeId] = array_values($roomTypeCounts);
        }
//        dump($counts);exit();

        return $counts;
    }

    public function getPackages()
    {
        $packageQueryCriteria = new PackageQueryCriteria();
        $packageQueryCriteria->hotel = $this->hotel;
        //$packageQueryCriteria->confirmed
        $packageQueryCriteria->filter = 'live_between';
        $packageQueryCriteria->liveBegin = $this->beginDate;
        $packageQueryCriteria->liveEnd = $this->endDate;
        if (count($this->roomTypeIds) > 0) {
            foreach ($this->roomTypeIds as $roomTypeId) {
                $packageQueryCriteria->addRoomTypeCriteria($roomTypeId);
            }
        }

        return $this->dm->getRepository('MBHPackageBundle:Package')->findByQueryCriteria($packageQueryCriteria);
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
        $roomTypes = count($this->roomTypeIds) > 0 ? $this->roomTypeIds : null;
        /** @var array [roomTypeId => RoomType] $roomsByRoomTypeIds */
        $roomsByRoomTypeIds = $this->dm->getRepository('MBHHotelBundle:Room')
            ->fetch($this->hotel, $roomTypes, $this->housingIds, $this->housingIds, null, null, true);

        foreach ($this->getRoomTypes() as $roomType) {

            /** @var RoomType $roomType */
            $roomTypeData[$roomType->getId()] = [
                'name' => $roomType->getName(),
                'rooms' =>$this->getRoomsData($roomsByRoomTypeIds, $roomType)
            ];
        }

        return $roomTypeData;
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
                    $houseDetails .= "Корпус \"" . $room->getHousing()->getName() . "\"<br>" ;
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