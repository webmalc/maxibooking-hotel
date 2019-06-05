<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;

class PriceCacheRepository extends DocumentRepository
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $categories
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $categories = false
    ) {
        $qb = $this->createQueryBuilder('q');

        $field = $categories ? 'roomType' : 'roomTypeCategory';
        $qb->field($field)->equals(null);

        // hotel
        if (!empty($hotel)) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // begin & end
        if (!empty($begin)) {
            $qb->field('date')->gte($begin);
        }
        if (!empty($end)) {
            $qb->field('date')->lte($end);
        }
        //roomTypes
        if (!empty($roomTypes)) {
            $field = $categories ? 'roomTypeCategory' : 'roomType';
            $qb->field($field . '.id')->in($roomTypes);
        }
        //tariffs
        if (!empty($tariffs)) {
            $qb->field('tariff.id')->in($tariffs);
        }
        //sort
        $qb->sort('date')->sort('hotel.id')->sort('roomType.id')->sort('tariff.id');

        return $qb;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
     * @param boolean $categories
     * @param Cache $memcached
     * @return array
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $grouped = false,
        $categories = false,
        Cache $memcached = null
    ) {
        if ($memcached) {
            $cache = $memcached->get('price_caches_fetch', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs, $categories)->getQuery()->execute();

        if (!$grouped) {
            if ($memcached) {
                $memcached->set(iterator_to_array($caches), 'price_caches_fetch', func_get_args());
            }

            return $caches;
        }

        $result = [];
        $method = $categories ? 'getRoomTypeCategory' : 'getRoomType';
        foreach ($caches as $cache) {
            $result[$cache->$method()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        if ($memcached) {
            $memcached->set($result, 'price_caches_fetch', func_get_args());
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param Hotel|null $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param bool $categories
     * @param \DateTime|null $displayedDate
     * @return array
     */
    public function fetchWithCancelDate(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $categories = false,
        \DateTime $displayedDate = null
    ) {
        $cachesQB = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs, $categories);
        if (!is_null($displayedDate)) {
            $cachesQB->addAnd($cachesQB->expr()
                ->addOr($cachesQB->expr()->field('createdAt')->exists(false))
                ->addOr($cachesQB->expr()->field('createdAt')->lt($displayedDate)));
            $cachesQB->addAnd($cachesQB->expr()
                ->addOr($cachesQB->expr()->field('cancelDate')->gt($displayedDate))
                ->addOr($cachesQB->expr()->field('cancelDate')->exists(false))
                ->addOr($cachesQB->expr()->field('cancelDate')->equals(null)));
        } else {
            $cachesQB->field('isEnabled')->equals(true);
        }
        $caches = $cachesQB->getQuery()->execute()->toArray();

        $result = [];
        $method = $categories ? 'getRoomTypeCategory' : 'getRoomType';
        /** @var PriceCache $cache */
        foreach ($caches as $cache) {
            $result[$cache->$method()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param string $roomTypeId
     * @param string $tariffId
     * @param bool $categories
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchRaw(
        \DateTime $begin = null,
        \DateTime $end = null,
        string $roomTypeId,
        string $tariffId,
        $categories = false
    )
    {
        $qb = $this->fetchQueryBuilder($begin, $end, null, [$roomTypeId], [$tariffId], $categories);

        return $qb->field('isEnabled')->equals(true)->hydrate(false)->sort(['date' => 'asc'])->getQuery()->execute()->toArray();
    }

    public function fetchRawPeriod(\DateTime $begin, \DateTime $end, array $roomTypeIds = [], array $tariffIds = [], bool $isUseCategory)
    {
        $qb = $this->fetchQueryBuilder($begin, $end, null, $roomTypeIds, $tariffIds, $isUseCategory);

        return $qb->field('isEnabled')->equals(true)->hydrate(false)->sort(['date' => 'asc'])->getQuery()->execute();
    }


}
