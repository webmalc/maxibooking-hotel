<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomCacheRepository extends DocumentRepository
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return int
     */
    public function getMinTotal(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff = null): int
    {
        $qb = $this->createQueryBuilder()
            ->field('date')->gte($begin)->lte($end)
            ->field('roomType.id')->equals($roomType->getId())
            ->sort('totalRooms')->limit(1);

        if ($tariff) {
            $qb->field('tariff')->references($tariff);
        } else {
            $qb->field('tariff')->equals(null);
        }

        $roomCache = $qb->getQuery()->getSingleResult();

        return $roomCache ? $roomCache->getTotalRooms() : 0;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param Hotel|null $hotel
     * @param array $roomTypes
     * @param bool|false $tariffs
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        $tariffs = false
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
        if (!empty($tariffs) && is_array($tariffs)) {
            $qb->field('tariff.id')->in($tariffs);
        }
        if ($tariffs === null) {
            $qb->field('tariff.id')->equals(null);
        }
        //sort
        $qb->sort('date')->sort('hotel.id')->sort('roomType.id');

        return $qb;
    }

    /**
     * @param \DateTime $date
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @return null|object
     */
    public function findOneByDate(\DateTime $date, RoomType $roomType, Tariff $tariff = null)
    {
        $qb = $this->createQueryBuilder('q');
        $qb
            ->field('date')->equals($date)
            ->field('roomType.id')->equals($roomType->getId())
        ;

        if ($tariff) {
            $qb->field('tariff.id')->equals($tariff->getId());
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param mixed $tariffs
     * @param boolean $grouped
     * @param Cache $memcached
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        $tariffs = false,
        $grouped = false,
        Cache $memcached = null
    ) {
        if ($memcached) {
            $cache = $memcached->get('room_cache_fetch', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            if ($memcached) {
                $memcached->set(iterator_to_array($caches), 'room_cache_fetch', func_get_args());
            }
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][!empty($cache->getTariff()) ? $cache->getTariff()->getId() : 0][$cache->getDate()->format('d.m.Y')] = $cache;
        }
        if ($memcached) {
            $memcached->set($result, 'room_cache_fetch', func_get_args());
        }

        return $result;
    }
}
