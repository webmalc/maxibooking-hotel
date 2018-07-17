<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    private $container;
    private $config;

    public function __construct(Helper $helper, ManagerRegistry $dm, ContainerInterface $container)
    {
        $this->container = $container;
        $this->helper = $helper;
        $this->dm = $dm;
        $this->config = $container->get('mbh.client_config_manager')->fetchConfig();
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
            $this->error = $this->container->get('translator')->trans('price.services.roomcachegraphgenerator.room_type_is_not_found');

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
        return $this;
    }


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
                'broken' => false,
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