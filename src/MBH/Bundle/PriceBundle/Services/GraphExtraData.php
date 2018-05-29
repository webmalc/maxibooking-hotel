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

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Restriction[][][]
     */
    private $restriction;


    private $period;

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
    public function get(Request $request, RoomCacheGraphGenerator $generator, Hotel $hotel): ?self
    {
        $this->hotel = $hotel;
        $this->generation = $generator;

        if (empty($this->generation->getDates())) {
            return null;
        }

        $tariffs = $request->get('tariffs');

        if (empty($tariffs)) {
            return $this->dataSimple();
        }

        $this->initVariables($tariffs);

        return $this->dataWithTariff();
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
        }

        return [
            'leftRooms'    => 0,
            'needArrivals' => 0,
        ];
    }

    /**
     * @param RoomType $roomType
     * @return array
     */
    public function getTariffs(RoomType $roomType): array
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

        $this->initPeriod();

        $this->initRoomCache();

        $this->initTariffsData($tariffs);

        $this->initRestriction();
    }

    private function initPeriod()
    {
        $periodRaw = new \DatePeriod($this->begin, new \DateInterval('P1D'), $this->generation->getEnd());
        $period = [];
        foreach ($periodRaw as $key => $date) {
            $period[$date->format('d.m.Y')] = $date;
        }

        $this->period = $period;
    }

    private function initRoomCache()
    {
        $this->roomsCache = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $this->begin,
                $this->generation->getEnd(),
                $this->hotel,
                [],
                false,
                true
            );
    }

    private function initTariffsData($tariffs)
    {
        $this->tariffsData = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->createQueryBuilder()
            ->hydrate(false)
            ->field('id')->in($tariffs)
            ->getQuery()
            ->toArray();
    }

    private function initRestriction()
    {
        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')
            ->fetchQueryBuilder($this->begin, $this->generation->getEnd(), $this->hotel)
            ->getQuery()
            ->toArray();

        $dataR = [];
        foreach ($restrictions as $restriction) {
            $dataR[$restriction->getDate()->format('d.m.Y')][$restriction->getRoomType()->getId()][$restriction->getTariff()->getId()] = $restriction;
        }

        $this->restriction = $dataR;
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
                foreach ($this->period as $key => $date) {
                    if (isset($this->restriction[$key][$roomTypeKey][$tariffKey])) {
                        /** @var \MBH\Bundle\PriceBundle\Document\Restriction $restriction */
                        $restriction = $this->restriction[$key][$roomTypeKey][$tariffKey];

                        $dateAsStr = $restriction->getDate()->format('d.m.Y');

                        $minStay = $restriction->getMinStay();

                        if ($restriction->getMinStayArrival() !== null) {
                            $minStay = $restriction->getMinStayArrival();
                        }

                        $currentDate = clone $restriction->getDate();

                    } else {
                        $minStay = 1;

                        $dateAsStr = $date->format('d.m.Y');

                        $currentDate = clone $date;
                    }

                    $oneDayAgo = (clone $currentDate)->modify('-1 day');

                    if (isset($roomCache[$oneDayAgo->format('d.m.Y')])) {
                        $yesterdayLeftRoom = $roomCache[$oneDayAgo->format('d.m.Y')]->getLeftRooms();
                    } else {
                        $yesterdayLeftRoom = 0;
                    }

                    if (isset($roomCache[$dateAsStr])) {
                        $leftRooms = $roomCache[$dateAsStr]->getLeftRooms();
                        $needArrivals = $leftRooms - $yesterdayLeftRoom;
                    } else {
                        $leftRooms = 0;
                        $needArrivals = 0;
                    }

                    $rawData[$dateAsStr] = [
                        'leftRooms'    => $leftRooms,
                        'needArrivals' => $needArrivals,
                    ];

                    $beginPeriod = clone $currentDate;
                    $beginPeriod->modify('+1 day');

                    $periodN = new \DatePeriod($beginPeriod, new \DateInterval('P1D'), $minStay - 2);
                    foreach ($periodN as $dateN) {
                        $isNecessary[$dateN->format('d.m.Y')][$tariffKey][] = $needArrivals < 0 ? 0 : $needArrivals;
                    }

                    if (isset($isNecessary[$dateAsStr][$tariffKey])) {
                        $isNecessarySum = array_sum($isNecessary[$dateAsStr][$tariffKey]);
                    } else {
                        $isNecessarySum = 0;
                    }

                    $rawTariffs[$tariffKey][$dateAsStr] = [
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