<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class RoomCacheRepository extends DocumentRepository
{
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
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param mixed $tariffs
     * @param boolean $grouped
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        $tariffs = false,
        $grouped = false
    ) {
        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][!empty($cache->getTariff()) ? $cache->getTariff()->getId() : 0][$cache->getDate()->format('d.m.Y')] = $cache;

        }

        return $result;
    }
}
