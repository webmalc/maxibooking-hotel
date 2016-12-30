<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PriceBundle\Document\Criteria\TariffQueryCriteria;

class TariffRepository extends DocumentRepository
{

    public function getMergingTariffs()
    {
        $result = $this->createQueryBuilder()
            ->field('defaultForMerging')->equals(true)
            ->field('isDefault')->equals(false)
            ->getQuery()
            ->execute()
        ;
        
        return $result;
    }

    /**
     * Get Tariffs with > 1 package
     * @return array
     */
    public function getWithPackages()
    {
        $ids = $this->getDocumentManager()
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->distinct('tariff.$id')
            ->getQuery()
            ->execute()
        ;

        return $this->createQueryBuilder()
            ->field('id')->in(iterator_to_array($ids))
            ->getQuery()
            ->execute()
            ;
    }

    /**
     * @param Hotel $hotel
     * @param array $type 'rooms', 'restrictions', 'prices'
     * @param array $tariffs ids
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchChildTariffsQuery(Hotel $hotel, $type, $tariffs = [])
    {
        $types = [
            'rooms' =>'inheritRooms', 'restrictions' => 'inheritRestrictions', 'prices' => 'inheritPrices'
        ];

        $qb = $this->createQueryBuilder();
        $qb->field('hotel.id')->equals($hotel->getId())
            ->addOr($qb->expr()->field('parent')->equals(null))
            ->addOr($qb->expr()->field('parent')->exists(false))
            ->addOr($qb->expr()->field('childOptions.' . $types[$type])->equals(false))
        ;

        // tariffs
        if (!empty($tariffs) && is_array($tariffs)) {
            $qb->field('id')->in($tariffs);
        }

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param array $type 'rooms', 'restrictions', 'prices'
     * @param array $tariffs ids
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchChildTariffs(Hotel $hotel, $type, $tariffs = [])
    {
        return $this->fetchChildTariffsQuery($hotel, $type, $tariffs)->getQuery()->execute();
    }

    /**
     * @param Hotel $hotel
     * @param mixed $online
     * @return array|null|object
     */
    public function fetchBaseTariff(Hotel $hotel, $online = null)
    {
        $qb = $this->createQueryBuilder('q');

        $qb->field('isDefault')->equals(true)
            ->field('hotel.id')->equals($hotel->getId())
            ->limit(1);

        if ($online !== null) {
            $qb->field('isOnline')->equals(boolval($online));
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Hotel $hotel
     * @param array $tariffs ids array
     * @param boolean $enabled
     * @param boolean $online
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(Hotel $hotel = null, $tariffs = null, $enabled = false, $online = false)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');

        // hotel
        if ($hotel) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // tariffs
        if (!empty($tariffs) && is_array($tariffs)) {
            $qb->field('id')->in($tariffs);
        }
        //enabled
        if ($enabled) {
            $qb->field('isEnabled')->equals(true);
        }
        //enabled
        if ($online) {
            $qb->field('isOnline')->equals(true);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');;

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param array $tariffs
     * @param boolean $enabled
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $tariffs = null, $enabled = false, $online = false)
    {
        return $this->fetchQueryBuilder($hotel, $tariffs, $enabled, $online)->getQuery()->execute();
    }

    /**
     * @param TariffQueryCriteria $criteria
     * @param int $offset
     * @param int $limit
     * @return Tariff[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByQueryCriteria(TariffQueryCriteria $criteria, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->queryCriteriaToBuilder($criteria);

        $queryBuilder
            ->skip($offset)
            ->limit($limit)
            ->sort('fullTitle', 'asc')
        ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param TariffQueryCriteria $criteria
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    private function queryCriteriaToBuilder(TariffQueryCriteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder();
        $currentDate = new \DateTime('midnight');

        if ($criteria->begin || $criteria->end) {
            $queryBuilder->field('begin')->lte($criteria->begin);
            $queryBuilder->field('end')->gte($criteria->end);
        }

        if (!empty($criteria->begin) && empty($criteria->end)) {
            $queryBuilder->field('begin')->lte($criteria->begin);
            $queryBuilder->field('end')->gte($currentDate);
        }

        if (empty($criteria->begin) && !empty($criteria->end)) {
            $queryBuilder->field('begin')->lte($currentDate);
            $queryBuilder->field('end')->gte($criteria->end);
        }

        return $queryBuilder;
    }
}
