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

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return iterable
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchCachedKeys(\DateTime $begin, \DateTime $end): iterable
    {
        $qb = $this->getInvalidateQB($begin, $end);

        return $qb
            ->distinct('cacheResultKey')
            ->getQuery()
            ->execute()
            ->toArray()
        ;

    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function removeItemsByDates(\DateTime $begin, \DateTime $end): void
    {
        $qb = $this->getInvalidateQB($begin, $end);
        $qb->remove()->getQuery()->execute();
    }

    private function getInvalidateQB(\DateTime $begin, \DateTime $end)
    {
        $qb = $this->createQueryBuilder();

        return $qb
            ->field('begin')->lte($end)
            ->field('end')->gt($begin)
            ;
    }


    public function flushCache(): void
    {
        $this->createQueryBuilder()->remove()->getQuery()->execute();
    }
}