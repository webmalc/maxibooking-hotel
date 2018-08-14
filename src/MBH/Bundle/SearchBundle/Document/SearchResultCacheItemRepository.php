<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchResultCacheItemRepository extends DocumentRepository
{
    public function fetchBySearchQuery(SearchQuery $searchQuery, bool $hydrate = false)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->field('tariffId')->equals($searchQuery->getTariffId())
            ->field('roomTypeId')->equals($searchQuery->getRoomTypeId())
            ->field('begin')->equals($searchQuery->getBegin())
            ->field('end')->equals($searchQuery->getEnd())
            ->field('adults')->equals($searchQuery->getAdults())
            ->field('children')->equals($searchQuery->getChildren())
            ->field('childrenAges')->equals($searchQuery->getChildrenAges())
        ;

        return $qb->hydrate($hydrate)->getQuery()->getSingleResult();
    }

    public function invalidateByDates(\DateTime $begin, \DateTime $end): void
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->remove()
            ->field('begin')->lte($end)
            ->field('end')->gt($begin)
            ->getQuery()
            ->execute()
        ;

    }


    public function flushCache(): void
    {
        $this->createQueryBuilder()->remove()->getQuery()->execute();
    }
}