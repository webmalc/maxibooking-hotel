<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\HotelBundle\Document\Hotel;

class PriceCacheRepository extends DocumentRepository
{
    /**
     * @param int $period
     * @return array
     */
    public function findForDashboard(int $period): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +' . $period . ' days');
        $result = [];
        $tariffs = $this->getDocumentManager()->getRepository('MBHPriceBundle:Tariff')
            ->getBaseTariffsIds();
        $caches =  $this->createQueryBuilder()
            ->select('hotel.id', 'roomType.id', 'tariff.id', 'date', 'price')
            ->field('date')->gte($begin)->lte($end)
            ->field('tariff.id')->in($tariffs)
            ->sort('date')->sort('hotel.id')->sort('roomType.id')
            ->hydrate(false)
            ->getQuery()
            ->execute()->toArray();

        foreach ($caches as $cache) {
            $cache['id'] = (string) $cache['_id'];
            $cache['date'] = $cache['date']->toDateTime();
            $cache['date']->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $cache['hotel'] = (string) $cache['hotel']['$id'];
            $cache['roomType'] = (string) $cache['roomType']['$id'];
            $cache['tariff'] = (string) $cache['tariff']['$id'];
            unset($cache['_id']);
            $result[$cache['hotel']][$cache['roomType']][$cache['tariff']][$cache['date']->format('d.m.Y')] = $cache;
        }

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
}
