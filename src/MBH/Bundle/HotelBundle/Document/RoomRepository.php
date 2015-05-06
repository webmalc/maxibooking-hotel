<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomRepository extends DocumentRepository
{
    /**
     * @param Hotel $hotel
     * @param null $roomType
     * @param null $skip
     * @param null $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQuery(Hotel $hotel = null, $roomType = null, $skip = null, $limit = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');

        // hotel
        if ($hotel) {
            $roomTypeIds = [];
            foreach ($hotel->getRoomTypes() as $hotelRoomType) {
                $roomTypeIds[] = $hotelRoomType->getId();
            }
            $qb->field('roomType.id')->in($roomTypeIds);
        }
        
        //roomType
        if (!empty($roomType)) {
            if (!$roomType instanceof RoomType) {
                $qb->field('roomType.id')->equals($roomType);
            } else {
                $qb->field('roomType.id')->equals($roomType->getId());
            }
        }

        //paging
        if ($skip !== null) {
            $qb->skip((int) $skip);
        }
        if ($limit !== null) {
            $qb->limit((int) $limit);
        }
        $qb->sort('roomType', 'asc');

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param null $roomType
     * @param null $skip
     * @param null $limit
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $roomType = null, $skip = null, $limit = null)
    {
        return $this->fetchQuery($hotel, $roomType, $skip, $limit)->getQuery()->execute();
    }


    /**
     * @return \Doctrine\MongoDB\ArrayIterator;
     */
    public function fetchHousings()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $docs = $qb->distinct('housing')
            ->getQuery()
            ->execute()
        ;

        return $docs;
    }

    /**
     * @return \Doctrine\MongoDB\ArrayIterator;
     */
    public function fetchFloors()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $docs = $qb->distinct('floors')
            ->getQuery()
            ->execute()
        ;

        return $docs;
    }
}
