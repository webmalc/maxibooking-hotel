<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\DocumentTraits\FindAllRawTrait;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;

class RoomTypeRepository extends DocumentRepository implements RoomTypeRepositoryInterface
{

    use FindAllRawTrait;

    /**
     * Get roomTypes with > 1 package
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
        $qb = $this->createQueryBuilder();

        // hotel
        if ($hotel) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // roomTypes
        if (!empty($roomTypes) && is_array($roomTypes)) {
            $qb->field('id')->in($roomTypes);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');

        return $qb;
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

    /**
     * @param array $roomTypeIds
     * @param array|null $hotelIds
     * @return array|RoomType[]
     */
    public function getByIdsAndHotelsIds(array $roomTypeIds = null, array $hotelIds = null)
    {
        $qb = $this->createQueryBuilder();

        if (!is_null($roomTypeIds)) {
            $qb->field('id')->in($roomTypeIds);
        }
        if (!is_null($hotelIds)) {
            $qb->field('hotel.id')->in($hotelIds);
        }

        return $qb->getQuery()->toArray();
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
     * @param string $query
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getByQueryName(string $query)
    {
        $qb = $this->createQueryBuilder();

        return $qb
            ->addOr($qb->expr()->field('title')->equals(new \MongoRegex('/^.*' . $query . '.*/ui')))
            ->addOr($qb->expr()->field('fullTitle')->equals(new \MongoRegex('/^.*' . $query . '.*/ui')))
            ->distinct('id')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function getByHotelsIdsAndFullTitle(array $hotelsIds, array $fullTitles)
    {
        $qb = $this->createQueryBuilder();
        if (\count($hotelsIds)) {
            $qb->field('hotel.id')->in($hotelsIds);
        }

        if (\count($fullTitles)) {
            $qb->field('fullTitle')->in($fullTitles);
        }
        return $qb
            ->getQuery()
            ->execute()
            ->toArray()
            ;
    }


}
