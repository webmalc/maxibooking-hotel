<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class TariffRepository extends DocumentRepository
{

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
}
