<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\AbstractBaseRepository;
use MBH\Bundle\BaseBundle\Lib\QueryCriteriaInterface;

class RoomRepository extends AbstractBaseRepository
{
    public function findByCriteria(QueryCriteriaInterface $criteria)
    {
        return;
    }
    
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param null $roomTypes
     * @param null $rooms
     * @param null $excludePackages
     * @param bool $grouped
     * @return array|mixed
     */
    public function fetchAccommodationRooms(\DateTime $begin, \DateTime $end, Hotel $hotel, $roomTypes = null, $rooms = null, $excludePackages = null, $grouped = false)
    {
        $dm = $this->getDocumentManager();
        $roomTypes and !is_array($roomTypes) ? $roomTypes = [$roomTypes] : $roomTypes;
        $rooms and !is_array($rooms) ? $rooms = [$rooms] : $rooms;
        $excludePackages and !is_array($excludePackages) ? $excludePackages = [$excludePackages] : $excludePackages;
        $ids = $groupedRooms = [];
        $hotelRoomTypes = [];
        $end = clone $end;
        $begin = clone $begin;

        foreach ($hotel->getRoomTypes() as $roomType) {
            if ($roomTypes && !in_array($roomType->getId(), $roomTypes)) {
                continue;
            }
            $hotelRoomTypes[] = $roomType->getId();
        }

        //packages with accommodation
        $packages = $dm->getRepository('MBHPackageBundle:Package')->fetchWithAccommodation($begin->modify('+1 day'), $end->modify('-1 day'), $rooms, $excludePackages);
        foreach ($packages as $package) {
            $ids[] = $package->getAccommodation()->getId();
        };

        // rooms
        $qb = $this->createQueryBuilder('r')->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc'])
             ->inToArray('roomType.id', $hotelRoomTypes)
             ->notInNotEmpty('id', $ids)
             ->inNotEmpty('id', $rooms)
        ;

        $roomDocs = $qb->getQuery()->execute();

        if (!$grouped) {
            return $roomDocs;
        }
        foreach ($roomDocs as $room) {
            $groupedRooms[$room->getRoomType()->getId()][] = $room;
        }

        return $groupedRooms;
    }

    /**
     * @param Hotel $hotel
     * @param mixed $roomTypes
     * @param mixed $housing
     * @param mixed $floor
     * @param int $skip
     * @param int $limit
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQuery(
        Hotel $hotel = null,
        $roomTypes = null,
        $housing = null,
        $floor = null,
        $skip = null,
        $limit = null
    ) {
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

        //roomTypes
        if (!empty($roomTypes)) {
            is_array($roomTypes) ? $roomTypes : $roomTypes = [$roomTypes];
            $qb->field('roomType.id')->in($roomTypes);
        }

        //housing
        if (!empty($housing)) {
            is_array($housing) ? $housing : $housing = [$housing];
            $qb->field('housing')->in($housing);
        }

        //floors
        if (!empty($floor)) {
            is_array($floor) ? $floor : $floor = [$floor];
            $qb->field('floor')->in($floor);
        }

        //paging
        if ($skip !== null) {
            $qb->skip((int)$skip);
        }
        if ($limit !== null) {
            $qb->limit((int)$limit);
        }
        $qb->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc']);

        return $qb;
    }

    /**
     * @param Hotel $hotel
     * @param mixed $roomTypes
     * @param mixed $housing
     * @param mixed $floor
     * @param int $skip
     * @param int $limit
     * @param boolean $group
     * @return array
     */
    public function fetch(
        Hotel $hotel = null,
        $roomTypes = null,
        $housing = null,
        $floor = null,
        $skip = null,
        $limit = null,
        $group = false
    ) {
        $result = $this->fetchQuery($hotel, $roomTypes, $housing, $floor, $skip, $limit)->getQuery()->execute();

        if ($group) {
            $grouped = [];
            foreach ($result as $doc) {
                $grouped[$doc->getRoomType()->getId()][] = $doc;
            }

            return $grouped;
        }

        return $result;
    }


    /**
     * @return array
     */
    public function fetchHousings()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $docs = $qb->distinct('housing')
            ->getQuery()
            ->execute();
        $docs = iterator_to_array($docs);
        asort($docs);

        return $docs;
    }

    /**
     * @return array;
     */
    public function fetchFloors()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder('s');
        $docs = $qb->distinct('floor')
            ->getQuery()
            ->execute();
        $docs = iterator_to_array($docs);
        asort($docs);

        return $docs;
    }
}
