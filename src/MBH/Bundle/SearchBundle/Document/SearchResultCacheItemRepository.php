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
     * @param array|null $roomTypeIds
     * @param array|null $tariffIds
     * @return iterable
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchCachedKeys(\DateTime $begin = null, \DateTime $end = null, ?array $roomTypeIds = [], ?array $tariffIds = []): iterable
    {
        $qb = $this->getInvalidateQB($begin, $end, $roomTypeIds, $tariffIds);

        return $qb
            ->distinct('cacheResultKey')
            ->getQuery()
            ->execute()
        ;

    }

    public function fetchIdByCacheKey(string $key)
    {
        return $this->createQueryBuilder()
            ->hydrate(false)
            ->distinct('id')
            ->field('cacheResultKey')->equals($key)
            ->getQuery()
            ->getSingleResult();

    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array|null $roomTypeIds
     * @param array|null $tariffIds
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function removeItemsByDates(?\DateTime $begin, ?\DateTime $end, ?array $roomTypeIds = [], ?array $tariffIds = []): void
    {
        $qb = $this->getInvalidateQB($begin, $end, $roomTypeIds, $tariffIds);
        $qb->remove()->getQuery()->execute();
    }

    private function getInvalidateQB(?\DateTime $begin, ?\DateTime $end, array $roomTypeIds = [], array $tariffIds = [])
    {
        $qb = $this->createQueryBuilder();
        if ($roomTypeIds) {
            $qb->field('roomTypeId')->in($roomTypeIds);
        }
        if ($tariffIds) {
            $qb->field('tariffId')->in($tariffIds);
        }
        if ($begin) {
            $qb->field('begin')->lte($end);
        }

        if ($end) {
            $qb->field('end')->gt($begin);
        }

        return $qb;
    }


    public function flushCache(): void
    {
        $this->createQueryBuilder()->remove()->getQuery()->execute();
    }

    public function countItems(): int
    {
        $count = $this->createQueryBuilder()->getQuery()->count();

        return $count;
    }
}