<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class PriceCacheRepository extends DocumentRepository
{
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
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
     * @return array
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $grouped = false
    ) {
        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        return $result;
    }
}
