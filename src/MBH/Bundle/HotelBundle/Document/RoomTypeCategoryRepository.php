<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\DocumentTraits\FindAllRawTrait;
use MBH\Bundle\HotelBundle\Model\RoomTypeRepositoryInterface;

class RoomTypeCategoryRepository extends DocumentRepository implements RoomTypeRepositoryInterface
{
    use FindAllRawTrait;

    /**
     * @param Hotel $hotel
     * @param mixed $roomTypesCats ids array
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQueryBuilder(Hotel $hotel = null, $roomTypesCats = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder();

        // hotel
        if ($hotel) {
            $qb->field('hotel.id')->equals($hotel->getId());
        }
        // roomTypes
        if (!empty($roomTypesCats) && is_array($roomTypesCats)) {
            $qb->field('id')->in($roomTypesCats);
        }
        $qb->sort('title', 'asc')->sort('fullTitle', 'asc');

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param null $roomTypesCats
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function fetch(Hotel $hotel = null, $roomTypesCats = null)
    {
        return $this->fetchQueryBuilder($hotel, $roomTypesCats)->getQuery()->execute();
    }

}
