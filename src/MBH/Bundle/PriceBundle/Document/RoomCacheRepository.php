<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class RoomCacheRepository extends DocumentRepository
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(\DateTime $begin, \DateTime $end, Hotel $hotel = null, $roomTypes = null)
    {
        $qb = $this->createQueryBuilder('q');

        // hotel
        if (!empty($hotel)) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // begin & end
        $qb->field('date')->gte($begin)
            ->field('date')->lte($end)
        ;
        //roomTypes
        if(!empty($roomTypes) && is_array($roomTypes)) {
            $qb->field('roomType.id')->in($roomTypes);
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
     * @param boolean $grouped
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetch(\DateTime $begin, \DateTime $end, Hotel $hotel = null, $roomTypes = null, $grouped = false)
    {
        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes)->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        return $result;
    }
}
