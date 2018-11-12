<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\SearchBundle\Document\SearchConditions;

class RestrictionRepository extends DocumentRepository
{

    public function fetchMinStay(\DateTime $date)
    {
        $date->modify('midnight');
        $tariffsIds = $hotelIds = $roomTypeIds = [];
        foreach ($this->dm->getRepository('MBHPriceBundle:Tariff')->findBy(['deletedAt' => null, 'isOnline' => true]) as $tariff) {
            $tariffsIds[] = $tariff->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['deletedAt' => null]) as $hotel) {
            $hotelIds[] = $hotel->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['deletedAt' => null]) as $roomType) {
            $roomTypeIds[] = $roomType->getId();
        }

        $qb = $this->createQueryBuilder();
        $qb
            ->field('date')->equals($date)
            ->field('tariff.id')->in($tariffsIds)
            ->field('hotel.id')->in($hotelIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('isEnabled')->equals(true)
        ;

        $data = $hotels = $categories = [];

        // roomTypes
        /** @var Restriction $restriction */
        $restrictions = $qb->getQuery()->execute();
        if (count($restrictions)) {
            foreach ($restrictions as $restriction) {
                if ($restriction->getTariff()->getIsDefault()) {
                    $minStay = $restriction->getMinStayArrival();
                    $hotel = $restriction->getRoomType()->getHotel();
                    $data['hotel_' . $hotel->getId()]['category_' . $restriction->getRoomType()->getCategory()->getId()] = $minStay;
                }
            };
        }

        return $data;
    }

    /**
     * @param Cache $memcached
     * @return array
     */
    public function fetchInOut(Cache $memcached = null)
    {


        if ($memcached) {
            $cache = $memcached->get('restrictions_in_out', []);
            if ($cache !== false) {
                return $cache;
            }
        }

        $tariffsIds = $hotelIds = $roomTypeIds = [];
        foreach ($this->dm->getRepository('MBHPriceBundle:Tariff')->findBy(['deletedAt' => null]) as $tariff) {
            $tariffsIds[] = $tariff->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['deletedAt' => null]) as $hotel) {
            $hotelIds[] = $hotel->getId();
        }

        foreach ($this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['deletedAt' => null]) as $roomType) {
            $roomTypeIds[] = $roomType->getId();
        }

        $data = $hotels = $categories = [];
        $qb = $this->createQueryBuilder();
        $qb
            ->field('date')->gte(new \DateTime('midnight'))
            ->field('date')->lte(new \DateTime('midnight +365 days'))
            ->field('tariff.id')->in($tariffsIds)
            ->field('hotel.id')->in($hotelIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('isEnabled')->equals(true)
            ->addOr($qb->expr()->field('closed')->equals(true))
            ->addOr(
                $qb->expr()
                    ->field('closedOnArrival')->equals(true)
                    ->field('closedOnDeparture')->equals(true)
            );

        // roomTypes
        foreach ($qb->getQuery()->execute() as $restriction) {
            if ($restriction->getTariff()->getIsDefault()) {
                $dateStr = $restriction->getDate()->format('d.m.Y');
                $hotel = $restriction->getRoomType()->getHotel();

                $data[$restriction->getRoomType()->getId()][$dateStr] = $dateStr;
                $data['allrooms_' . $hotel->getId()][$dateStr] = $dateStr;
                $hotels[$hotel->getId()] = $hotel;
                $category = $restriction->getRoomType()->getCategory();
                if ($category) {
                    $data['category_' . $category->getId()][$dateStr] = $dateStr;
                    $categories[$category->getId()] = $category;
                }
            }
        };


        // hotels
        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                isset($data[$roomType->getId()]) ? $dates = $data[$roomType->getId()] : $dates = [];
                $data['allrooms_' . $hotel->getId()] = array_intersect($data['allrooms_' . $hotel->getId()], $dates);
            }
        }
        // roomTypeCategories
        foreach ($categories as $category) {
            foreach ($category->getTypes() as $roomType) {
                isset($data[$roomType->getId()]) ? $dates = $data[$roomType->getId()] : $dates = [];
                $data['category_' . $category->getId()] = array_intersect($data['category_' . $category->getId()], $dates);
            }
        }

        if ($memcached) {
            $memcached->set($data, 'restrictions_fetch', []);
        }

        return $data;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = []
    ) {

        $qb = $this->createQueryBuilder('q');

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
            $qb->field('roomType.id')->in($roomTypes);
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
     * @param \DateTime $date
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param Cache $memcached
     * @return Restriction
     */
    public function findOneByDate(\DateTime $date, RoomType $roomType, Tariff $tariff, Cache $memcached = null)
    {
        if ($memcached) {
            $cache = $memcached->get('restrictions_find_one_by_date', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $qb = $this->findOneByDateQB($date, $roomType, $tariff);
        $result = $qb->getQuery()->getSingleResult();

        if ($memcached) {
            $memcached->set($result, 'restrictions_find_one_by_date', func_get_args());
        }

        return $result;
    }

    public function findOneByDateRaw(\DateTime $date, RoomType $roomType, Tariff $tariff)
    {
        $qb = $this->findOneByDateQB($date, $roomType, $tariff);
        $qb->select('minStayArrival');
        return $qb->hydrate(false)->getQuery()->getSingleResult();
    }

    private function findOneByDateQB(\DateTime $date, RoomType $roomType, Tariff $tariff)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->field('date')->equals($date)
            ->field('tariff.id')->equals($tariff->getId())
            ->field('roomType.id')->equals($roomType->getId())
            ->field('isEnabled')->equals(true)
        ;

        return $qb;
    }



    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
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
        Cache $memcached = null
    ) {

        if ($memcached) {
            $cache = $memcached->get('restrictions_fetch', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            if ($memcached) {
                $memcached->set(iterator_to_array($caches), 'restrictions_fetch', func_get_args());
            }

            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        if ($memcached) {
            $memcached->set($result, 'restrictions_fetch', func_get_args());
        }

        return $result;
    }

    /**
     * @param SearchConditions $conditions
     * @param bool $isCaterory
     * @return array
     */
    public function getAllSearchPeriod(SearchConditions $conditions, bool $isCaterory): array
    {
        $qb = $this->createQueryBuilder();
        $restrictionTariffs = $conditions->getRestrictionTariffs();
        $isTariffIds = (bool)$restrictionTariffs->count();
        if ($isTariffIds) {
            $tariffIds = Helper::toIds($restrictionTariffs);
            $qb->field('tariff.id')->in(array_unique($tariffIds));
        }

        $isRoomTypeSpecified = $conditions->getRoomTypes()->count();

        if ($isRoomTypeSpecified) {
            //** TODO: rebuild here the function signature (pass roomType and tariff, but  not conditions) */
            $roomTypes = [];
            if ($isCaterory) {
                foreach ($conditions->getRoomTypes() as $category) {
                    /** @var RoomTypeCategory $category */
                    $types = $category->getTypes();
                    foreach ($types as $roomType) {
                        $roomTypes[] = $roomType;
                    }
                }
            } else {
                $roomTypes = $conditions->getRoomTypes();
            }

            $roomTypeIds = Helper::toIds($roomTypes);
            $qb->field('roomType.id')->in($roomTypeIds);
        }

        /** Priority to tariff or roomTpe */
        $isHotelIds = $conditions->getHotels()->count();
        if (!$isTariffIds && !$isRoomTypeSpecified && $isHotelIds) {
            $hotelIds = Helper::toIds($conditions->getHotels());
            $qb->field('hotel.id')->in($hotelIds);
        }

        $qb
            ->field('date')->gte($conditions->getMaxBegin())
            ->field('date')->lte($conditions->getMaxEnd());

        return $qb->hydrate(false)->getQuery()->toArray();
    }
}
