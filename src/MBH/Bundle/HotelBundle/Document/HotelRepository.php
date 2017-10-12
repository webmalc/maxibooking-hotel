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
     */
    public function getLastHotel(): ?Hotel
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->field('deletedAt')->exists(false)
            ->sort('createdAt', 'DESC');
        /** @var Hotel $hotel */
        $hotel = $qb->getQuery()->getSingleResult();

        return $hotel;
    }

    public function getHotelWithFilledContacts()
    {
        return $this->createQueryBuilder()
            ->field('contactInformation')->exists(true)->notEqual(null)
            ->getQuery()->getSingleResult();
    }
}