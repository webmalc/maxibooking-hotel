<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Cursor;
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

    /**
     * @param array $hotelsIds
     * @param bool $isEmptyAsAll
     * @return Cursor|Hotel[]
     */
    public function getByIds(array $hotelsIds, $isEmptyAsAll = true)
    {
        $qb = $this->createQueryBuilder();
        if (!(count($hotelsIds) == 0 && $isEmptyAsAll)) {
            $qb->field('id')->in($hotelsIds);
        }

        return $qb->getQuery()->execute();
    }

    public function getByFullTitle(array $fullTitles)

    {
        $qb = $this->createQueryBuilder();
        return $qb
            ->field('fullTitle')
            ->in($fullTitles)
            ->getQuery()
            ->execute();
    }
}