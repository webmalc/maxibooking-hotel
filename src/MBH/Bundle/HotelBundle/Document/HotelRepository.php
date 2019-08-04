<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\DocumentTraits\FindAllRawTrait;

/**
 * Class HotelRepository
 */
class HotelRepository extends DocumentRepository
{

    use FindAllRawTrait;

    /**
     * Get last Hotel or null
     *
     * @return array|null|object
     */
    public function getLastHotel()
    {
        $qb = $this->createQueryBuilder()->sort('createdAt', 'desc')->limit(1)->getQuery()->getSingleResult()->execute(
        );

        return $qb ?? null;
    }

    public function getHotelsByIds(array $ids)
    {
        return $this
            ->createQueryBuilder()
            ->hydrate(true)
            ->field('id')
            ->in($ids)
            ->getQuery()
            ->execute();
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

    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getSearchActiveIds(): array
    {
        return $this->createQueryBuilder()
            ->field('isSearchActive')->equals(true)
            ->distinct('id')
            ->getQuery()
            ->execute()
            ->toArray();
    }


}