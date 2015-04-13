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
    public function fetchQueryBuilder(\DateTime $begin, \DateTime $end, Hotel $hotel = null, $roomTypes = null, $tariffs = null)
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
        //tariffs
        if(!empty($tariffs) && is_array($tariffs)) {
            $qb->field('tariff.id')->in($tariffs);
        }
        //sort
        $qb->sort('date')->sort('hotel.id')->sort('tariff.id')->sort('roomType.id');

        return $qb;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetch(\DateTime $begin, \DateTime $end, Hotel $hotel = null, $roomTypes = null, $tariffs = null, $grouped = false)
    {
        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getTariff()->getId()][$cache->getRoomType()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        return $result;
    }
}
