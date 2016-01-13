<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\HttpFoundation\Request;

class RoomCacheGraphGenerator
{
    /**
     * @var array
     */
    private $roomTypes;

    /**
     * @var array
     */
    private $dates;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ManagerRegistry
     */
    private $dm;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @var string
     */
    private $error;

    /**
     * @var array
     */
    private $maxTotalRooms = [];

    /**
     * @var Tariff
     */
    private $tariff;

    public function __construct(Helper $helper, ManagerRegistry $dm)
    {
        $this->helper = $helper;
        $this->dm = $dm;
        $this->config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    public function getBegin()
    {
        return $this->begin;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function generate(Request $request, Hotel $hotel)
    {
        $this->begin = $this->helper->getDateFromString($request->get('begin'));
        $this->hotel = $hotel;
        $this->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($this->hotel);

        if(!$this->begin) {
            $this->begin = new \DateTime('00:00');
        }
        $this->end = $this->helper->getDateFromString($request->get('end'));
        if(!$this->end || $this->end->diff($this->begin)->format("%a") > 366 || $this->end <= $this->begin) {
            $this->end = clone $this->begin;
            $this->end->modify('+45 days');
        }
        $to = clone $this->end;
        $to->modify('+1 day');
        $period = new \DatePeriod($this->begin, \DateInterval::createFromDateString('1 day'), $to);

        //get roomTypes
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
            ->fetch($hotel, $request->get('roomTypes'))
        ;
        if (!count($roomTypes)) {
            $this->error = 'Типы номеров не найдены';

            return $this;
        }

        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $this->begin, $this->end, $hotel,
                $request->get('roomTypes') ? $request->get('roomTypes') : [],
                false, true)
        ;

        foreach ($roomTypes as $roomType) {
            $this->addRoomType($roomType);

            foreach ($period as $day) {
                $this->addDate($day)->addInfo($day, $roomType, $roomCaches);
            }
        }
        return $this->checkWindows($request);
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function checkWindows(Request $request)
    {
        if (!$this->config || !$this->config->getSearchWindows()) {
            return $this;
        }

        $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
            $this->begin, $this->end, $this->hotel,
            $request->get('roomTypes') ? $request->get('roomTypes') : [],
            [$this->tariff->getId()],
            true
        );

        if (!count($restrictions)) {
            return $this;
        }

        foreach ($this->data as $roomTypeId => $days) {
            foreach ($days as $day => $info) {
                $this->checkWindow($info, $days, $restrictions, true);
                $this->checkWindow($info, $days, $restrictions, false);
            }
        }

        return $this;
    }

    private function checkWindow(array $info, $days, array $restrictions, $right = true)
    {
        $date = $info['date'];
        $restriction = null;
        $middle = $info['packageCount'];
        if (!$info['leftRooms']) {
            return true;
        }

        if (isset($restrictions[$info['roomType']->getId()][$this->tariff->getId()][$date->format('d.m.Y')])) {
            $restriction = $restrictions[$info['roomType']->getId()][$this->tariff->getId()][$date->format('d.m.Y')];
        }

        if (!$restriction || !$restriction->getMinStayArrival()) {
            return true;
        }
        if ($restriction->getClosed() || ($right && $restriction->getClosedOnDeparture()) || (!$right && $restriction->getClosedOnArrival())) {
            return true;
        }
        $len = $restriction->getMinStayArrival();

        if ($right) {
            $from = clone $date;
            $to = clone $date;
            $to->modify('+ ' . $len . ' days');
            $to->modify('-1 day');
        } else {
            $from = clone $date;
            $to = clone $date;
            $to->modify('-1 day');
            $from->modify('- ' . $len . ' days');
        }
        $to->modify('+1 day');
        $period = new \DatePeriod($from, \DateInterval::createFromDateString('1 day'), $to);

        $greater = 0;
        $less = 0;
        foreach ($period as $day) {
            if (!isset($days[$day->format('d.m.Y')])) {
                return true;
            }
            $data = $days[$day->format('d.m.Y')];

            if ($data['packageCount'] >= $middle) {
                $greater += 1;
            } else {
                $less +=1;
            }
        }

        if ($greater && $less) {
            foreach ($period as $day) {
                $this->data[$info['roomType']->getId()][$day->format('d.m.Y')]['broken'] = true;
            }
        }
    }

    /**
     * @param array $info
     * @param array $days
     * @param array $restrictions
     * @param bool $right
     * @return bool
     */
    /*private function checkWindow(array $info, array $days, array $restrictions, $right = true)
    {
        $begin = $info['date'];
        $restriction = null;
        if (isset($restrictions[$info['roomType']->getId()][$this->tariff->getId()][$begin->format('d.m.Y')])) {
            $restriction = $restrictions[$info['roomType']->getId()][$this->tariff->getId()][$begin->format('d.m.Y')];
        }

        if (!$restriction || !$restriction->getMinStayArrival()) {
            return true;
        }
        if ($restriction->getClosed() || ($right && $restriction->getClosedOnDeparture()) || (!$right && $restriction->getClosedOnArrival())) {
            return true;
        }

        $end = clone $begin;
        $end->modify('+' . $restriction->getMinStayArrival() + 1 .' days');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $switchCount = $ascCount = $descCount = 0;
        $prevNum = null;

        foreach ($period as $day) {
            if (!isset($days[$day->format('d.m.Y')])) {
                return $this;
            }
            $data = $days[$day->format('d.m.Y')];

            $num = $data['leftRooms'];

            if ($prevNum !== null && $num != $prevNum) {

                $switchCount++;

                if ($num > $prevNum) {
                    $ascCount++;
                }
                if ($num < $prevNum) {
                    $descCount++;
                }
            }
            $prevNum = $num;
        }

        if ($switchCount > 1 && $descCount > 0 && $ascCount > 0) {
            foreach ($period as $day) {
                $this->data[$info['roomType']->getId()][$day->format('d.m.Y')]['broken'] = true;
            }
        }
    }*/


    /**
     * @return mixed
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * @param RoomType $roomType
     * @return $this
     */
    private function addRoomType(RoomType $roomType)
    {
        $this->roomTypes[$roomType->getId()] = $roomType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    private function addDate(\DateTime $date)
    {
        $this->dates[$date->format('d.m.Y')] = $date;

        return $this;
    }

    /**
     * @param \DateTime $date
     * @param RoomType $roomType
     * @param array $caches
     * @return $this
     */
    private function addInfo(\DateTime $date, RoomType $roomType, array $caches)
    {
        $r = $roomType->getId();
        $d = $date->format('d.m.Y');

        if (isset($caches[$r][0][$d])) {
            $c = $caches[$r][0][$d];
            $this->data[$r][$d] = [
                'date' => $date,
                'roomType' => $roomType,
                'packageCount' => $c->getPackagesCount(),
                'leftRooms' => $c->getLeftRooms(),
                'totalRooms' => $c->getTotalRooms(),
                'broken' => false
            ];

            if (!isset($this->maxTotalRooms[$r]) || $c->getTotalRooms() > $this->maxTotalRooms[$r]) {
                $this->maxTotalRooms[$r] = $c->getTotalRooms();
            }
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     * @param RoomType $roomType
     * @return int
     */
    public function getInfo(RoomType $roomType, \DateTime $date)
    {
        $r = $roomType->getId();
        $d = $date->format('d.m.Y');

        if (isset($this->data[$r][$d])) {
            return $this->data[$r][$d];
        }

        return null;
    }

    /**
     * @param RoomType $roomType
     * @return int
     */
    public function getMaxTotalRooms(RoomType $roomType)
    {
        $r = $roomType->getId();

        if (isset($this->maxTotalRooms[$r])) {
            return $this->maxTotalRooms[$r];
        }

        return 0;
    }
}