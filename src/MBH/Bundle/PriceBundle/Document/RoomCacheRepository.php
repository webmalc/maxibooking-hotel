<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomCacheRepository extends DocumentRepository
{
    /**
     * @param int $period
     * @return array
     */
    public function findForDashboard(int $period): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . $period . ' days');
        $result = [];
        $caches =  $this->createQueryBuilder()
            ->select('hotel.id', 'roomType.id', 'tariff.id', 'date', 'totalRooms')
            ->field('date')->gte($begin)->lte($end)
            ->sort('date')->sort('hotel.id')->sort('roomType.id')
            ->hydrate(false)
            ->getQuery()
            ->execute()->toArray();

        foreach ($caches as $cache) {
            $cache['id'] = (string) $cache['_id'];
            $cache['date'] = $cache['date']->toDateTime();
            $cache['date']->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $cache['hotel'] = (string) $cache['hotel']['$id'];
            $cache['roomType'] = (string) $cache['roomType']['$id'];
            $cache['tariff'] = isset($cache['tariff']) ? (string) $cache['tariff']['$id'] : 0;
            unset($cache['_id']);
            $result[$cache['hotel']][$cache['roomType']][$cache['tariff']][$cache['date']->format('d.m.Y')] = $cache;
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Cache $memcached
     * @return int
     */
    public function getMinTotal(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff = null, Cache $memcached = null): int
    {
        if ($memcached) {
            $cache = $memcached->get('room_cache_min_total', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

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
        $result = $roomCache ? $roomCache->getTotalRooms() : 0;
        if ($memcached) {
            $memcached->set($result, 'room_cache_min_total', func_get_args());
        }

        return $result;
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
        $qb = $this->createQueryBuilder();

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
        $qb = $this->createQueryBuilder();
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
     * @return array|\Doctrine\ODM\MongoDB\Query\Builder|Cursor
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
        /** @var RoomCache $cache */
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][!empty($cache->getTariff()) ? $cache->getTariff()->getId() : 0][$cache->getDate()->format('d.m.Y')] = $cache;
        }
        if ($memcached) {
            $memcached->set($result, 'room_cache_fetch', func_get_args());
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $selectedFields
     * @return array
     */
    public function getRawExistedRoomCaches(\DateTime $begin, \DateTime $end, array $selectedFields = [])
    {
        $qb = $this->fetchQueryBuilder($begin, $end, null, [], null);
        if (count($selectedFields) > 0) {
            $qb->select($selectedFields);
        }

        return $qb
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
