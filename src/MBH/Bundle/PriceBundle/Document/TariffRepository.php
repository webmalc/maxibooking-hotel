<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class TariffRepository extends DocumentRepository
{

    public function fetchBaseTariff(Hotel $hotel, \DateTime $begin, $online = true)
    {
        $qb = $this->createQueryBuilder('q');

        $qb->field('isDefault')->equals(true)
            ->field('hotel.id')->equals($hotel->getId())
            ->field('isOnline')->equals($online)
            ->addOr(
                $qb->expr()
                    ->field('end')->gte($begin)
                    ->field('begin')->lte($begin)
            )
            ->limit(1);

        return $qb->getQuery()->getSingleResult();
    }
}
