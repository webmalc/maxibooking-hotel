<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Menu\Builder;

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
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function getQBWithAvailable()
    {
        return $this
            ->createQueryBuilder()
            ->field('isEnabled')->equals(true);
    }

    /**
     * @param array $hotelsIds
     * @param bool $isEmptyAsAll
     * @return Cursor|Hotel[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getByIds(array $hotelsIds, $isEmptyAsAll = true)
    {
        $qb = $this->createQueryBuilder();
        if (!(count($hotelsIds) == 0 && $isEmptyAsAll)) {
            $qb->field('id')->in($hotelsIds);
        }

        return $qb->getQuery()->execute();
    }
}