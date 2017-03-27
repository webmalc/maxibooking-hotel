<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\PriceBundle\Lib\TariffFilter;
use MBH\Bundle\BaseBundle\Service\Cache;

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
     * @param string $tariffId
     * @param Cache $memcached
     * @return Tariff|null
     */
    public function fetchById(string $tariffId, Cache $memcached = null)
    {
        if ($memcached) {
            $cache = $memcached->get('tariffs_fetch_by_id', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $queryBuilder = $this->createQueryBuilder('q');

        $queryBuilder
            ->field('id')->equals($tariffId)
            ->limit(1);

        $result = $queryBuilder->getQuery()->getSingleResult();

        if ($memcached) {
            $memcached->set($result, 'tariffs_fetch_by_id', func_get_args());
        }

        return $result;
    }

    /**
     * @param Hotel $hotel
     * @param mixed $online
     * @param Cache $memcached
     * @return array|null|object
     */
    public function fetchBaseTariff(Hotel $hotel, $online = null, Cache $memcached = null)
    {
        if ($memcached) {
            $cache = $memcached->get('tariffs_fetch_base', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $queryBuilder = $this->createQueryBuilder('q');

        $queryBuilder->field('isDefault')->equals(true)
            ->field('hotel.id')->equals($hotel->getId())
            ->limit(1);

        if ($online !== null) {
            $queryBuilder->field('isOnline')->equals(boolval($online));
        }

        $result = $queryBuilder->getQuery()->getSingleResult();

        if ($memcached) {
            $memcached->set($result, 'tariffs_fetch_base', func_get_args());
        }

        return $result;
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
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');
        ;

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param array $tariffs
     * @param boolean $enabled
     * @param Cache $memcached
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $tariffs = null, $enabled = false, $online = false, Cache $memcached = null)
    {
        if ($memcached) {
            $cache = $memcached->get('tariffs_fetch_method', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }
        $result = $this->fetchQueryBuilder($hotel, $tariffs, $enabled, $online)
            ->getQuery()->execute();

        if ($memcached) {
            $memcached->set(iterator_to_array($result), 'tariffs_fetch_method', func_get_args());
        }

        return $result;
    }

    /**
     * @param TariffFilter $filter
     * @return Builder
     */
    public function getFilteredQueryBuilder(TariffFilter $filter): Builder
    {
        $qb = $this->createQueryBuilder();

        if ($filter->getSearch()) {
            $fullNameRegex = new \MongoRegex('/.*' . $filter->getSearch() . '.*/ui');
            $qb->field('fullTitle')->equals($fullNameRegex);
        }

        if ($filter->getBegin()) {
            $qb->addAnd($qb->expr()->addOr(
                $qb->expr()->field('end')->exists(true)->gte($filter->getBegin()),
                $qb->expr()->field('end')->equals(null)
            ));
        }

        if ($filter->getEnd()) {
            $qb->addAnd($qb->expr()->addOr(
                $qb->expr()->field('begin')->exists(true)->lte($filter->getEnd()),
                $qb->expr()->field('begin')->equals(null)
            ));
        }

        if (!$filter->getIsEnabled()) {
            $qb->field('isEnabled')->equals(true);
        }

        if ($filter->getIsOnline() === 1) {
            $qb->field('isOnline')->equals(true);
        } elseif ($filter->getIsOnline() === 0) {
            $qb->field('isOnline')->equals(false);
        }

        if ($filter->getHotel()) {
            $qb->field('hotel')->references($filter->getHotel());
        }

        $qb->sort(['position' => 'desc', 'fullTitle' => 'asc']);

        return $qb;
    }

    /**
     * @param TariffFilter $filter
     * @return CursorInterface
     */
    public function getFiltered(TariffFilter $filter): CursorInterface
    {
        $qb = $this->getFilteredQueryBuilder($filter);

        return $qb->getQuery()->execute();
    }
}
