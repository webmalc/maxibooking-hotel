<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\PriceBundle\Lib\TariffFilter;

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
     * @return Tariff|null
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
            $qb->field('end')->exists(true)->gte($filter->getBegin());
        }

        if ($filter->getEnd()) {
            $qb->field('begin')->exists(true)->lte($filter->getEnd());
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
