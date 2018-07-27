<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;

class RoomTypeRepository extends DocumentRepository implements RoomTypeRepositoryInterface
{
    /**
     * Get roomTypes with > 1 package
     * @return array
     */
    public function getWithPackages()
    {
        $ids = $this->getDocumentManager()
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->distinct('roomType.$id')
            ->getQuery()
            ->execute();

        return $this->createQueryBuilder()
            ->field('id')->in(iterator_to_array($ids))
            ->getQuery()
            ->execute();
    }

    /**
     * @param Hotel $hotel
     * @param mixed $roomTypes ids array
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(Hotel $hotel = null, $roomTypes = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');

        // hotel
        if ($hotel) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // roomTypes
        if (!empty($roomTypes) && is_array($roomTypes)) {
            $qb->field('id')->in($roomTypes);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');;

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param null $roomTypes
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $roomTypes = null)
    {
        return $this->fetchQueryBuilder($hotel, $roomTypes)->getQuery()->execute();
    }

    /**
     * @param Hotel $hotel
     * @param null $categories
     * @return mixed
     */
    public function getByCategories(Hotel $hotel = null, $categories = null)
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($categories) {
            $queryBuilder->field('category.id')->in($categories);
        }

        if (!is_null($hotel)) {
            $queryBuilder->field('hotel.id')->equals($hotel->getId());
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function getByCapacity(int $adults, int $children)
    {
        $roomTypes = $this->findAll();
        $result = array_filter(
            $roomTypes,
            function (RoomType $roomType) use ($adults, $children) {

                return $roomType->getTotalPlaces() >= ($adults + $children);
            }
        );

        return $result;

    }

    public function fetchRaw(array $roomTypeIds, array $hotelIds): array
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder();

        // hotel
        if (\is_array($hotelIds) && !empty($hotelIds)) {
            $qb->field('hotel.id')->in($hotelIds);
        }
        // roomTypes
        if (!empty($roomTypeIds) && \is_array($roomTypeIds)) {
            $qb->field('id')->in($roomTypeIds);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');

        return $qb->hydrate(false)->getQuery()->toArray();
    }

    /**
     * @param array $categoryIds
     * @param array $hotelIds
     * @return array
     */
    public function fetchRawWithCategory(array $categoryIds, array $hotelIds): array
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder();

        // hotel
        if (\is_array($hotelIds) && !empty($hotelIds)) {
            $qb->field('hotel.id')->in($hotelIds);
        }
        // roomTypes
        if (!empty($categoryIds) && \is_array($categoryIds)) {
            $qb->field('category.id')->in($categoryIds);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');

        return  $qb->hydrate(false)->getQuery()->toArray();
    }

    public function findAllWithHotels(): array
    {
        $qb = $this->createQueryBuilder();

        return $qb->field('hotel')->prime(true)->getQuery()->toArray();
    }

}
