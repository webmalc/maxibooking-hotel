<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\MongoDB\ArrayIterator;
use MBH\Bundle\BaseBundle\Document\AbstractBaseRepository;
use MBH\Bundle\BaseBundle\Lib\QueryCriteriaInterface;
use MBH\Bundle\BaseBundle\Service\Cache;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

/**
 * Class RoomRepository

 */
class RoomRepository extends AbstractBaseRepository
{
    public function findByCriteria(QueryCriteriaInterface $criteria)
    {
        return;
    }

    public function getVirtualRoomsForPackageQB(Package $package = null)
    {
        $qb = $this->createQueryBuilder();
        if ($package) {
            $virtualRoom = $package->getVirtualRoom();
            $begin = $package->getBegin();
            $end = $package->getEnd();
            $packages = $this
                ->getDocumentManager()
                ->getRepository('MBHPackageBundle:Package')
                ->fetchWithVirtualRooms($begin, $end, $package->getRoomType())
            ;

            $rooms = array_map(function ($p) use ($virtualRoom, $begin, $end) {
                $id = $p->getVirtualRoom()->getId();

                if ($p->getBegin() == $end || $p->getEnd() == $begin) {
                    return null;
                }

                if (!$virtualRoom || $id != $virtualRoom->getId()) {
                    return $id;
                }

                return null;

            }, iterator_to_array($packages));

            $qb->field('roomType')->references($package->getRoomType())
                ->field('id')->notIn($rooms)
                ->sort(['fullTitle', 'title'])
            ;
        }

        return $qb;
    }


    /**
     * Return available distinct hotel floors
     * @param Hotel $hotel
     * @return string[]
     */
    public function getFloorsByHotel(Hotel $hotel)
    {
        /** @var ArrayIterator $result */
        $result = $this->createQueryBuilder()
            ->field('hotel.id')->equals($hotel->getId())
            ->distinct('floor')
            ->getQuery()->execute();
        return $result->toArray();
    }

    /**
     * @param Room[] $rooms
     * @return array
     */
    public function optGroupRooms(array $rooms)
    {
        $result = [];
        foreach ($rooms as $roomTypeRooms) {
            $result[$roomTypeRooms[0]->getRoomType()->getName()] = [];
            foreach ($roomTypeRooms as $room) {
                $result[$roomTypeRooms[0]->getRoomType()->getName()][$room->getId()] = $room;
            }
        }

        return $result;
    }


    /**
     * @param Hotel $hotel
     * @param bool|true $grouped
     * @return mixed
     */
    public function getRoomsByType(Hotel $hotel, $grouped = true)
    {
        $groupedRooms = null;
        $hotelRoomTypes = [];
        foreach ($hotel->getRoomTypes() as $roomType) {
            $hotelRoomTypes[] = $roomType->getId();
        }

        // rooms
        $qb = $this->createQueryBuilder('r')
            ->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc'])
        ;
        if($hotelRoomTypes) {
            $qb->inToArray('roomType.id', $hotelRoomTypes);
        }

        $roomDocs = $qb->getQuery()->execute();

        if (!$grouped) {
            return $roomDocs;
        }
        foreach ($roomDocs as $room) {
            $groupedRooms[$room->getRoomType()->getId()][] = $room;
        }

        return $groupedRooms;
    }

//    public function fetchAccommodationRoomsForPackage(Package $package, Hotel $hotel)
//    {
//        $begin = $package->getLastEndAccommodation();
//        $end = $package->getEnd();
//        $interval = $end->diff($begin, true);
//        if (!$interval->format('%d')) {
//            return [];
//        }
//        $excludePackages = $package->getId();
//
//        return $this->fetchAccommodationRooms($begin, $end, $hotel, null, null, $excludePackages, true);
//    }
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param null $roomTypes
     * @param null $rooms
     * @param null $excludePackages
     * @param bool $grouped
     * @param Cache $memcached
     * @return array|mixed
     */
    public function fetchAccommodationRooms(
        \DateTime $begin, \DateTime $end, Hotel $hotel, $roomTypes = null,
        $rooms = null, $excludePackages = null, $grouped = false, Cache $memcached = null
    )
    {
        if ($memcached) {
            $cache = $memcached->get('accommodation_rooms', func_get_args());
            if ($cache !== false) {
                return $cache;
            }
        }

        $dm = $this->getDocumentManager();
        $filter = $this->dm->getFilterCollection()->isEnabled('softdeleteable');


        if (!$filter) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }

        $roomTypes && !is_array($roomTypes) ? $roomTypes = [$roomTypes] : $roomTypes;
        $rooms && !is_array($rooms) ? $rooms = [$rooms] : $rooms;
        $excludePackages and !is_array($excludePackages) ? $excludePackages = [$excludePackages] : $excludePackages;
        $fullRoomsIds = $groupedRooms = [];
        $hotelRoomTypes = [];
        $newEnd = clone $end;
        $newBegin = clone $begin;

        foreach ($hotel->getRoomTypes() as $roomType) {
            if ($roomTypes && !in_array($roomType->getId(), $roomTypes) || !$roomType->getIsEnabled()) {
                continue;
            }
            $hotelRoomTypes[] = $roomType->getId();
        }

        $existedAccommodations = $dm
            ->getRepository('MBHPackageBundle:PackageAccommodation')
            ->getWithAccommodationQB(
                $newBegin->modify('+1 day'),
                $newEnd->modify('-1 day'),
                $rooms,
                $excludePackages)
            ->select(['accommodation.id'])
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();


        foreach ($existedAccommodations as  $accommodation) {
            /** @var PackageAccommodation $accommodation */
            $fullRoomsIds[] = $accommodation['accommodation']['$id'];
        }

        // rooms
        $qb = $this->createQueryBuilder()->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc'])
             ->field('isEnabled')->equals(true)
             ->inToArray('roomType.id', $hotelRoomTypes)
             ->notInNotEmpty('id', $fullRoomsIds)
             ->inNotEmpty('id', $rooms)
        ;

        $roomDocs = $qb->getQuery()->execute();

        if (!$grouped) {
            if ($memcached) {
                $memcached->set(iterator_to_array($roomDocs), 'accommodation_rooms', func_get_args());
            }

            return $roomDocs;
        }
        foreach ($roomDocs as $room) {
            $groupedRooms[$room->getRoomType()->getId()][] = $room;
        }

        if (!$filter && $this->dm->getFilterCollection()->enable('softdeleteable')) {
            $this->dm->getFilterCollection()->disable('softdeleteable');
        }

        if ($memcached) {
            $memcached->set($groupedRooms, 'accommodation_rooms', func_get_args());
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
     * @param bool $group
     * @param bool $isEnabled
     * @param array|null $sort
     * @param Cache|null $cache
     * @return array
     */
    public function fetch(
        Hotel $hotel = null,
        $roomTypes = null,
        $housing = null,
        $floor = null,
        $skip = null,
        $limit = null,
        $group = false,
        $isEnabled = null,
        array $sort = null,
        Cache $cache = null
    )
    {
        if ($cache) {
            $cacheEntry = $cache->get('rooms_fetch', func_get_args());
            if ($cacheEntry !== false) {
                return $cacheEntry;
            }
        }

        $result = $this->fetchQuery($hotel, $roomTypes, $housing, $floor, $skip, $limit, $isEnabled, $sort)->getQuery()->execute();

        if ($group) {
            $grouped = [];
            foreach ($result as $doc) {
                $grouped[$doc->getRoomType()->getId()][] = $doc;
            }

            if ($cache) {
                $cache->set($grouped, 'rooms_fetch', func_get_args());
            }

            return $grouped;
        }

        if ($cache) {
            $cache->set(iterator_to_array($result), 'rooms_fetch', func_get_args());
        }

        return $result;
    }

    /**
     * @param Hotel|null $hotel
     * @param null $roomTypes
     * @param null $housing
     * @param null $floor
     * @param null $skip
     * @param null $limit
     * @param null $isEnabled
     * @param array|null $sort
     * @return \Doctrine\ODM\MongoDB\Query\Builder|\MBH\Bundle\BaseBundle\Lib\QueryBuilder
     */
    public function fetchQuery(
        Hotel $hotel = null,
        $roomTypes = null,
        $housing = null,
        $floor = null,
        $skip = null,
        $limit = null,
        $isEnabled = null,
        array $sort = null
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
            $qb->field('housing.id')->in($housing);
        }

        //floors
        if (!empty($floor)) {
            is_array($floor) ? $floor : $floor = [$floor];
            $qb->field('floor')->in($floor);
        }
        
        //Is enabled
        if ($isEnabled !== null) {
            $qb->field('isEnabled')->equals($isEnabled);
        }

        //paging
        if ($skip !== null) {
            $qb->skip((int)$skip);
        }
        if ($limit !== null) {
            $qb->limit((int)$limit);
        }
        $qb->field('deletedAt')->equals(null)
            ->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc']);

        if ($sort) {
            $qb->sort($sort);
        }

        return $qb;
    }

    /**
     * @return array;
     */
    public function fetchFloors()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $qb = $this->createQueryBuilder();
        $docs = $qb->distinct('floor')
            ->getQuery()
            ->execute();
        $docs = iterator_to_array($docs);
        asort($docs);

        return $docs;
    }

    /**
     * @param array|null $statusIds
     * @param bool $includeWithoutStatuses
     * @param bool $isOnlyEnabled
     * @return array
     */
    public function getNumberOfRoomsByRoomTypeIds($statusIds = null, $includeWithoutStatuses = true, $isOnlyEnabled = false)
    {
        $qb = $this->createQueryBuilder();
        if ($isOnlyEnabled) {
            $qb->field('isEnabled')->equals(true);
        }
        if ($includeWithoutStatuses) {
            $qb->addOr($qb->expr()->field('status.0')->exists(false));
        }
        if (!is_null($statusIds)) {
            $qb->addOr($qb->expr()->field('status.id')->in($statusIds));
        }
        
        $roomsQuantityByRoomTypeIds = $qb
            ->map('function() {
                var roomTypeId = this.roomType.$id;
                emit(roomTypeId.valueOf(), this);
            }')
            ->reduce('function(key, values) {
                return values.length;
            }')
            ->getQuery()
            ->execute()
            ->toArray();

        $result = [];
        foreach ($roomsQuantityByRoomTypeIds as $roomsQuantityData) {
            $result[$roomsQuantityData['_id']] = (int)$roomsQuantityData['value'];
        }

        return $result;
    }

    /**
     * @param array $housingsIds
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getRoomsIdsByHousingsIds(array $housingsIds)
    {
        return $this
            ->createQueryBuilder()
            ->field('housing.id')->in($housingsIds)
            ->distinct('id')
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
