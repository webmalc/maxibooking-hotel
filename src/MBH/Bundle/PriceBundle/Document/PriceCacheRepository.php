<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class PriceCacheRepository extends DocumentRepository
{
    /**
     * @param int $period
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findForDashboard(int $period, bool $isUseCategory = false): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . $period . ' days');
        $tariffs = $this->getDocumentManager()->getRepository('MBHPriceBundle:Tariff')
            ->getBaseTariffsIds();

        $roomTypeField = $isUseCategory ? 'roomTypeCategory' : 'roomType';

        $caches =  $this->createQueryBuilder()
            ->select('hotel.id', $roomTypeField.'.id', 'tariff.id', 'date', 'price')
            ->field('date')->gte($begin)->lte($end)
            ->field('tariff.id')->in($tariffs)
            ->sort('date')->sort('hotel.id')->sort($roomTypeField.'.id')
            ->field($roomTypeField)->exists(true)
            ->hydrate(false)
            ->getQuery()
            ->execute()->toArray();

        return $this->convertRawMongoData($caches, $roomTypeField);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param null $roomTypeIds
     * @param null $tariffIds
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRawByRoomTypesAndTariffs(\DateTime $begin, \DateTime $end, $roomTypeIds = null, $tariffIds = null)
    {
        $cachesQb =  $this
            ->createQueryBuilder()
            ->select('hotel.id', 'roomType.id', 'tariff.id', 'date', 'price')
            ->field('date')->gte($begin)->lte($end)
            ->sort('date')->sort('hotel.id')->sort('roomType.id')
            ->hydrate(false);

        if (!is_null($roomTypeIds)) {
            $cachesQb->field('roomType.id')->in($roomTypeIds);
        }

        if (!is_null($tariffIds)) {
            $cachesQb->field('tariff.id')->in($tariffIds);
        }

        $result = $this->convertRawMongoData($cachesQb->getQuery()->execute());

        return $result;
    }

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
        $qb = $this->createQueryBuilder();

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

        return $qb->field('isEnabled')->equals(true)->hydrate(false)->sort(['date' => 'asc'])->getQuery()->execute()->toArray();
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

        $caches = $this->fetchQueryBuilder(
            $begin,
            $end,
            $hotel,
            $roomTypes,
            $tariffs,
            $categories
        )->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        $method = $categories ? 'getRoomTypeCategory' : 'getRoomType';
        /** @var PriceCache $cache */
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
     * @param RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array|null $tariffsIds
     * @return PriceCache|null|object
     */
    public function getWithMinPrice(RoomType $roomType, \DateTime $begin, \DateTime $end, array $tariffsIds = null)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->field('date')->gte($begin)->lte($end)
            ->field('roomType.id')->equals($roomType->getId())
            ->sort('price')
            ->field('isEnabled')->equals(true)
            ->limit(1)
        ;
        if (!is_null($tariffsIds)) {
            $qb->field('tariff.id')->in($tariffsIds);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param $caches
     * @return array
     */
    private function convertRawMongoData($caches, $roomTypeKey = 'RoomType')
    {
        $result = [];
        foreach ($caches as $cache) {
            $cache['id'] = (string)$cache['_id'];
            $cache['date'] = $cache['date']->toDateTime();
            $cache['date']->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $cache['hotel'] = (string)$cache['hotel']['$id'];
            $cache[$roomTypeKey] = (string)$cache[$roomTypeKey]['$id'];
            $cache['tariff'] = (string)$cache['tariff']['$id'];
            unset($cache['_id']);
            $result[$cache['hotel']][$cache['roomType']][$cache['tariff']][$cache['date']->format('d.m.Y')] = $cache;
        }

        return $result;
    }
}
