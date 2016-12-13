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

    public function fetchAccommodationRoomsForPackage(Package $package, Hotel $hotel)
    {
        $begin = $package->getCurrentAccommodationBegin();
        $end = $package->getEnd();
        $interval = $end->diff($begin, true);
        if (!$interval->format('%d')) {
            return [];
        }
        $excludePackages = $package->getId();

        return $this->fetchAccommodationRooms($begin, $end, $hotel, null, null, $excludePackages, true);
    }
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
        $ids = $groupedRooms = [];
        $hotelRoomTypes = [];
        $newEnd = clone $end;
        $newBegin = clone $begin;

        foreach ($hotel->getRoomTypes() as $roomType) {
            if ($roomTypes && !in_array($roomType->getId(), $roomTypes)) {
                continue;
            }
            $hotelRoomTypes[] = $roomType->getId();
        }

        //packages with accommodation //Как тут быть, когда accomodation выбраны.
//        $packages = $dm->getRepository('MBHPackageBundle:Package')->fetchWithAccommodation(
//            $newBegin->modify('+1 day'), $newEnd->modify('-1 day'), $rooms, $excludePackages);
//        foreach ($packages as $package) {
//            /** @var Package $package */
//            foreach ($package->getAccommodations() as $accommodation) {
//                /** @var PackageAccommodation $accommodation */
//                $ids[] = $accommodation->getAccommodation()->getId();
//            }
//
//        };
        $pAccommodations = $dm
            ->getRepository('MBHPackageBundle:Package')
            ->fetchWithAccommodation(
                $newBegin->modify('+1 day'),
                $newEnd->modify('-1 day'),
                $rooms,
                $excludePackages);
        foreach ($pAccommodations as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            $ids[] = $accommodation->getAccommodation()->getId();
        }

        // rooms
        $qb = $this->createQueryBuilder('r')->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc'])
             ->field('isEnabled')->equals(true)
             ->inToArray('roomType.id', $hotelRoomTypes)
             ->notInNotEmpty('id', $ids)
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
        $isEnabled = null
    )
    {
        $result = $this->fetchQuery($hotel, $roomTypes, $housing, $floor, $skip, $limit, $isEnabled)->getQuery()->execute();

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
     * @param Hotel $hotel
     * @param mixed $roomTypes
     * @param mixed $housing
     * @param mixed $floor
     * @param int $skip
     * @param int $limit
     * @param bool $isEnabled
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function fetchQuery(
        Hotel $hotel = null,
        $roomTypes = null,
        $housing = null,
        $floor = null,
        $skip = null,
        $limit = null,
        $isEnabled = null
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
        $qb->sort(['roomType.id' => 'asc', 'fullTitle' => 'asc']);

        return $qb;
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
