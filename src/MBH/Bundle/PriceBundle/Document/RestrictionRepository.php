<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RestrictionRepository extends DocumentRepository
{
    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchInOut()
    {

        $tariffsIds = $hotelIds = $roomTypeIds = [];
        foreach ($this->dm->getRepository('MBHPriceBundle:Tariff')->findBy(['deletedAt' => null]) as $tariff) {
            $tariffsIds[] = $tariff->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['deletedAt' => null]) as $hotel) {
            $hotelIds[] = $hotel->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['deletedAt' => null]) as $roomType) {
            $roomTypeIds[] = $roomType->getId();
        }

        $data = $hotels = [];
        $qb = $this->createQueryBuilder('q');
        $qb
            ->field('date')->gte(new \DateTime('midnight'))
            ->field('date')->lte(new \DateTime('midnight +365 days'))
            ->field('tariff.id')->in($tariffsIds)
            ->field('hotel.id')->in($hotelIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('isEnabled')->equals(true)
            ->addOr($qb->expr()->field('closed')->equals(true))
            ->addOr(
                $qb->expr()
                    ->field('closedOnArrival')->equals(true)
                    ->field('closedOnDeparture')->equals(true)
            );

        foreach ($qb->getQuery()->execute() as $restriction) {
            if ($restriction->getTariff()->getIsDefault()) {
                $dateStr = $restriction->getDate()->format('d.m.Y');
                $hotel = $restriction->getRoomType()->getHotel();

                $data[$restriction->getRoomType()->getId()][$dateStr] = $dateStr;
                $data['allrooms_' . $hotel->getId()][$dateStr] = $dateStr;
                $hotels[$hotel->getId()] = $hotel;
            }
        };

        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                isset($data[$roomType->getId()]) ? $dates = $data[$roomType->getId()] : $dates = [];
                $data['allrooms_' . $hotel->getId()] = array_intersect($data['allrooms_' . $hotel->getId()], $dates);
            }
        }

        return $data;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = []
    ) {
    
        $qb = $this->createQueryBuilder('q');

        // hotel
        if (!empty($hotel)) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // begin & end
        if (!empty($begin)) {
            $qb->field('date')->gte($begin);
        }
        if (!empty($end)) {
            $qb->field('date')->lte($end);
        }
        //roomTypes
        if (!empty($roomTypes)) {
            $qb->field('roomType.id')->in($roomTypes);
        }
        //tariffs
        if (!empty($tariffs)) {
            $qb->field('tariff.id')->in($tariffs);
        }
        //sort
        $qb->sort('date')->sort('hotel.id')->sort('roomType.id')->sort('tariff.id');

        return $qb;
    }

    /**
     * @param \DateTime $date
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param Cache $memcached
     * @return Restriction
     */
    public function findOneByDate(\DateTime $date, RoomType $roomType, Tariff $tariff, Cache $memcached = null)
    {
        if ($memcached) {
            $cache = $memcached->get('restrictions_find_one_by_date', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $qb = $this->createQueryBuilder('q');
        $qb
            ->field('date')->equals($date)
            ->field('tariff.id')->equals($tariff->getId())
            ->field('roomType.id')->equals($roomType->getId());

        $result = $qb->getQuery()->getSingleResult();

        if ($memcached) {
            $memcached->set($result, 'restrictions_find_one_by_date', func_get_args());
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
     * @param Cache $memcached
     * @return array
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $grouped = false,
        Cache $memcached = null
    ) {
    
        if ($memcached) {
            $cache = $memcached->get('restrictions_fetch', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            if ($memcached) {
                $memcached->set(iterator_to_array($caches), 'restrictions_fetch', func_get_args());
            }

            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        if ($memcached) {
            $memcached->set($result, 'restrictions_fetch', func_get_args());
        }

        return $result;
    }
}
