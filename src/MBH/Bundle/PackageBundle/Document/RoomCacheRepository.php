<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomCacheRepository extends DocumentRepository
{
    public function fetch(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null, array $roomTypesIds = null, array $tariffsIds = null, $prices = true, array $sort  = null)
    {
        $qb = $this->createQueryBuilder('q');
        
        //roomTypes
        if (is_array($roomTypesIds)) {
            $qb->field('roomType.id')->in($roomTypesIds);
        }
        //roomType
        if ($roomType) {
            $qb->field('roomType.id')->equals($roomType->getId());
        }
        //tariffs
        if (is_array($tariffsIds)) {
            $qb->field('tariff.id')->in($tariffsIds);
        }
        // with prices
        if ($prices) {
            $qb->where("function() { return this.prices.length > 0; }");
        }
        // dates
        if ($begin) {
            $qb->field('date')->gte($begin);
        }
        if ($end) {
            $qb->field('date')->lte($end);
        }
        // sort
        if (is_array($sort)) {
            $qb->sort($sort);
        } else {
            $qb->sort(['roomType.id' => 'asc', 'date' => 'asc']);
        }
        
        $result = $qb->getQuery()->execute();
        
        return $result;
    }
}
