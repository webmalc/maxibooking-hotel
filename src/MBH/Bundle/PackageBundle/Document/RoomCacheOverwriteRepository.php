<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;

class RoomCacheOverwriteRepository extends DocumentRepository
{
    public function findStructured(\DateTime $begin = null, \DateTime $end = null, $tariff = null, $roomType = null, $enabled = false)
    {
        $qb = $this->createQueryBuilder('s')
        ->sort('date', 'asc')
        ;

        if ($begin && $end) {
            $qb->field('date')->range($begin, $end);
        }
        if ($tariff && $tariff instanceof Tariff) {
            $qb->field('tariff.id')->equals($tariff->getId());
        }
        if ($tariff && is_array($tariff)) {
            $qb->field('tariff.id')->in($tariff);
        }
        if ($roomType && $roomType instanceof RoomType) {
            $qb->field('roomType.id')->equals($roomType->getId());
        }
        if ($roomType && is_array($roomType)) {
            $qb->field('roomType.id')->in($roomType);
        }
        if($enabled) {
            $qb->field('isEnabled')->equals(true);
        }

        $result = [];
        foreach ($qb->getQuery()->execute() as $doc) {
            $result[$doc->getTariff()->getId()][$doc->getRoomType()->getId()][$doc->getDate()->format('d.m.Y')] = $doc;
        }

        return $result;
    }
}
