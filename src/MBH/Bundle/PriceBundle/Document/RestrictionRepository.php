<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RestrictionRepository extends DocumentRepository
{
    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetchInOut()
    {
        $data = $hotels = [];
        $qb = $this->createQueryBuilder('q');
        $qb
            ->field('date')->gte(new \DateTime('midnight'))
            ->field('date')->lte(new \DateTime('midnight +365 days'))
            ->field('isEnabled')->equals(true)
            ->addOr($qb->expr()->field('closed')->equals(true))
            ->addOr(
                $qb->expr()
                    ->field('closedOnArrival')->equals(true)
                    ->field('closedOnDeparture')->equals(true)
            );

        foreach ($qb->getQuery()->execute() as $restriction)
        {
            if ($restriction->getTariff()->getIsDefault()) {
                $dateStr = $restriction->getDate()->format('d.m.Y');
                $hotel = $restriction->getRoomType()->getHotel();

                $data[$restriction->getRoomType()->getId()][$dateStr] = $dateStr;
                $data['allrooms_' . $hotel->getId()][$dateStr] = $dateStr;
                $hotels[$hotel->getId()] = $hotel;
            }
        };

        foreach ($hotels as $hotel) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                isset($data[$roomType->getId()]) ? $dates = $data[$roomType->getId()] : $dates = [];
                $data['allrooms_' . $hotel->getId()] = array_intersect($data['allrooms_' . $hotel->getId()], $dates);
            }
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
    )
    {
        $qb = $this->createQueryBuilder('q');

        // hotel
        if (!empty($hotel)) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // begin & end
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
     * @return null|object
     */
    public function findOneByDate(\DateTime $date, RoomType $roomType, Tariff $tariff)
    {
        $qb = $this->createQueryBuilder('q');
        $qb
            ->field('date')->equals($date)
            ->field('tariff.id')->equals($tariff->getId())
            ->field('roomType.id')->equals($roomType->getId());;

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param array $roomTypes
     * @param array $tariffs
     * @param boolean $grouped
     * @return array
     */
    public function fetch(
        \DateTime $begin = null,
        \DateTime $end = null,
        Hotel $hotel = null,
        array $roomTypes = [],
        array $tariffs = [],
        $grouped = false
    )
    {
        $caches = $this->fetchQueryBuilder($begin, $end, $hotel, $roomTypes, $tariffs)->getQuery()->execute();

        if (!$grouped) {
            return $caches;
        }
        $result = [];
        foreach ($caches as $cache) {
            $result[$cache->getRoomType()->getId()][$cache->getTariff()->getId()][$cache->getDate()->format('d.m.Y')] = $cache;
        }

        return $result;
    }
}
