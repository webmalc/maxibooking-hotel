<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class HotelRepository
 */
class HotelRepository extends DocumentRepository
{

    /**
     * Get last Hotel or null
     *
     * @return array|null|object
     */
    public function getLastHotel()
    {
        $qb = $this->createQueryBuilder()->sort('createdAt', 'desc')->limit(1)->getQuery()->getSingleResult()->execute();

        return $qb ?? null;
    }

}