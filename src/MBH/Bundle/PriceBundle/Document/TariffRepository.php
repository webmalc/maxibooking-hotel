<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

class TariffRepository extends DocumentRepository
{

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
     * @param mixed $tariffs ids array
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(Hotel $hotel = null, $tariffs = null)
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
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');;

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param null $tariffs
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $tariffs = null)
    {
        return $this->fetchQueryBuilder($hotel, $tariffs)->getQuery()->execute();
    }
}
