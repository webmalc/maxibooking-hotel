<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 02.04.18
 * Time: 12:49
 */

namespace MBH\Bundle\PriceBundle\Services;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Возвращает объект с данными кол-ве свободных номеров (leftRooms) и кол-ве необходимых заездов (needArrivals).
 * Если переданы id тарифов, то так-же возвращается
 * "необходимого количества свободных номеров для бронирования без окон" (isNecessary) и
 * разница (diff) между свободными номерами и (isNecessary)
 *
 * (needArrivals), расчитываемым по:
 * "разница между кол-вом не забронированных номеров на текущую дату и кол-вом не забронированных номеров на предыдущую дату
 *  примечание: если на предыдущую дату номера в продажу не выставлены, то их количество приравнивается к 0"
 *
 * расчет (isNecessary) указан в issue #1432
 */

class GraphExtraData
{
    private const DAYS_FOR_RESTRICTION = 7;

    /**
     * @var array
     */
    private $hotel;

    /**
     * @var RoomCacheGraphGenerator
     */
    private $generation;

    /**
     * @var ManagerRegistry
     */
    private $dm;

    /**
     * @var array
     */
    private $rawData;

    /**
     * @var array
     */
    private $tariffs;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\RoomCache[]
     */
    private $roomsCache;

    /**
     * @var array
     */
    private $tariffsData;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var bool
     */
    private $withTariff = false;

    public function __construct(ManagerRegistry $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param Request $request
     * @param RoomCacheGraphGenerator $generator
     * @param Hotel $hotel
     * @return GraphExtraData|null
     */
    public function get(Request $request, RoomCacheGraphGenerator $generator, Hotel $hotel): self
    {
        $this->hotel = $hotel;
        $this->generation = $generator;

        if (empty($this->generation->getDates())) {
            return null;
        }

        $tariffs = $request->get('tariffs');

        if (empty($tariffs)) {
            return $this->dataSimple();
        } else {
            $this->initVariables($tariffs);
            return $this->dataWithTariff();
        }
    }

    /**
     * @return bool
     */
    public function withTariff(): bool
    {
        return $this->withTariff;
    }

    public function getData(RoomType $roomType, \DateTime $date): array
    {
        $roomTypeKey = $roomType->getId();

        if (isset($this->rawData[$roomTypeKey][$date->format('d.m.Y')])) {
            return $this->rawData[$roomTypeKey][$date->format('d.m.Y')];
        } else {
            return [
                'leftRooms'    => 0,
                'needArrivals' => 0,
            ];
        }
    }

    /**
     * @param RoomType $roomType
     * @return array
     */
    public function getTariffs(RoomType $roomType):array
    {
        return $this->tariffs[$roomType->getId()];
    }

    /**
     * @param $tariffs
     */
    private function initVariables($tariffs)
    {
        $this->withTariff = true;
        $this->begin = clone $this->generation->getBegin();
        $this->begin->modify('-' . self::DAYS_FOR_RESTRICTION . ' day');

        $this->roomsCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $this->begin,
                $this->generation->getEnd(),
                $this->hotel,
                [],
                false,
                true
            );

        $this->tariffsData = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->createQueryBuilder('t')
            ->hydrate(false)
            ->field('id')->in($tariffs)
            ->getQuery()
            ->toArray();
    }

    /**
     * @return GraphExtraData
     */
    private function dataWithTariff(): self
    {
        foreach ($this->generation->getRoomTypes() as $roomTypeKey => $roomTypeData) {

            $roomCache = $this->roomsCache[$roomTypeKey][0];

            $rawData = [];
            $isNecessary = [];
            $rawTariffs = [];

            foreach ($this->tariffsData as $tariffKey => $tariffData) {

                $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')
                    ->fetchQueryBuilder($this->begin, $this->generation->getEnd(), $this->hotel, [$roomTypeKey], [$tariffKey])
                    ->getQuery()
                    ->toArray();

                /** @var \MBH\Bundle\PriceBundle\Document\Restriction $restriction */
                foreach ($restrictions as $restriction) {
                    $restDate = $restriction->getDate();
                    $restDateAsStr = $restDate->format('d.m.Y');

                    $minStay = $restriction->getMinStay();

                    if ($restriction->getMinStayArrival() !== null) {
                        $minStay = $restriction->getMinStayArrival();
                    }

                    $currentDate = clone $restriction->getDate();
                    $oneDayAgo = $currentDate->modify('-1 day');

                    if (isset($roomCache[$oneDayAgo->format('d.m.Y')])) {
                        $yesterdayLeftRoom = $roomCache[$oneDayAgo->format('d.m.Y')]->getLeftRooms();
                    } else {
                        $yesterdayLeftRoom = 0;
                    }

                    if (isset($roomCache[$restDateAsStr])) {
                        $leftRooms = $roomCache[$restDateAsStr]->getLeftRooms();
                        $needArrivals = $leftRooms - $yesterdayLeftRoom;
                    } else {
                        $leftRooms = 0;
                        $needArrivals = 0;
                    }

                    $rawData[$restDateAsStr] = [
                        'leftRooms'    => $leftRooms,
                        'needArrivals' => $needArrivals,
                    ];


                    $beginPeriod = clone $restDate;
                    $beginPeriod->modify('+1 day');

                    $period = new \DatePeriod($beginPeriod, new \DateInterval('P1D'), $minStay - 2);
                    foreach ($period as $date) {
                        $isNecessary[$date->format('d.m.Y')][$tariffKey][] = $needArrivals < 0 ? 0 : $needArrivals;
                    }

                    if (isset($isNecessary[$restDateAsStr][$tariffKey])) {
                        $isNecessarySum = array_sum($isNecessary[$restDateAsStr][$tariffKey]);
                    } else {
                        $isNecessarySum = 0;
                    }

                    $rawTariffs[$tariffKey][$restDateAsStr] = [
                        'isNecessary' => $isNecessarySum,
                        'diff'        => $leftRooms - $isNecessarySum,
                    ];

                }

                $rawTariffs[$tariffKey] = $this->classForTariffData($tariffData, $rawTariffs[$tariffKey]);
            }

            $this->rawData[$roomTypeKey] = $rawData;

            $this->tariffs[$roomTypeKey] = $rawTariffs;
        }

        return $this;
    }

    /**
     * @param $tariff
     * @param $data
     */
    private function classForTariffData($tariff, $data)
    {
        return new class($tariff, $data)
        {
            private $tariff;
            private $data;

            public function __construct($tariff, $data)
            {
                $this->tariff = $tariff;
                $this->data = $data;
            }

            public function getId()
            {
                return $this->tariff['_id'];
            }

            public function getFullTitle()
            {
                return $this->tariff['fullTitle'];
            }

            public function getData(\DateTime $date)
            {
                if (isset($this->data[$date->format('d.m.Y')])) {
                    return $this->data[$date->format('d.m.Y')];
                } else {
                    return [
                        'isNecessary' => 0,
                        'diff'        => 0,
                    ];
                }
            }
        };
    }

    /**
     * @return GraphExtraData
     */
    private function dataSimple(): self
    {
        $extraData = [];
        foreach ($this->generation->getRoomTypes() as $roomTypeKey => $roomTypeData) {
            foreach ($this->generation->getDates() as $dateKey => $dateData) {

                if (($currentRoom = $this->generation->getInfo($roomTypeData, $dateData)) !== null) {
                    $currentDate = clone $currentRoom['date'];
                    $oneDayAgo = $currentDate->modify('-1 day');

                    if ($this->generation->getInfo($roomTypeData, $oneDayAgo) !== null) {
                        $room = $this->dm->getRepository('MBHPriceBundle:RoomCache')
                            ->findOneByDate($oneDayAgo, $roomTypeData);
                    } else {
                        $room = $this->generation->getInfo($roomTypeData, $oneDayAgo);
                    }

                    if (empty($room)) {
                        $oneDayAgoLeftRooms = 0;
                    } else {
                        $oneDayAgoLeftRooms = $room->getLeftRooms();
                    }

                    $leftRooms = $currentRoom['leftRooms'];
                    $needArrivals = $leftRooms - $oneDayAgoLeftRooms;
                } else {
                    $leftRooms = 0;
                    $needArrivals = 0;
                }


                $extraData[$roomTypeKey][$dateKey] = [
                    'leftRooms'    => $leftRooms,
                    'needArrivals' => $needArrivals,
                ];
            }
        }
        $this->rawData = $extraData;

        return $this;
    }
}