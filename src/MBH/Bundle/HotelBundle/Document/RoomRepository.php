<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\RoomType;

class RoomRepository extends DocumentRepository
{
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
            if (count($roomTypeIds)) {
                $qb->field('roomType.id')->in($roomTypeIds);
            }

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

}
