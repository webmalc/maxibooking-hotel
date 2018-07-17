<?php


namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RoomTypeReport

 */
class RoomTypeReport
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    /**
     * @param RoomTypeReportCriteria $criteria
     * @return RoomTypeReportResult
     */
    public function findByCriteria(RoomTypeReportCriteria $criteria)
    {
        $doctrineCriteria = ['hotel.id' => $criteria->hotel];
        if($criteria->roomType) {
            $doctrineCriteria['id'] = $criteria->roomType;
        }

        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $this->dm->getRepository('MBHHotelBundle:RoomType');
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        /** @var RoomType[] $roomTypes */
        $roomTypes = $roomTypeRepository->findBy($doctrineCriteria);

        $roomTypeIDs = [];
        foreach($roomTypes as $roomType) {
            $roomTypeIDs[] = $roomType->getId();
        }
        /** @var RoomRepository $roomRepository */
        $roomRepository = $this->dm->getRepository('MBHHotelBundle:Room');
        $queryBuilder = $roomRepository->createQueryBuilder()
            ->field('roomType.id')->in($roomTypeIDs);

        if($criteria->housing) {
            $queryBuilder->field('housing.id')->equals($criteria->housing);
        }
        if($criteria->floor) {
            $queryBuilder->field('floor')->equals($criteria->floor);
        }

        $queryBuilder->field('isEnabled')->equals(true);

        /** @var Room[] $rooms */
        $rooms = $queryBuilder->getQuery()->execute();

        $result = new RoomTypeReportResult();

        $result->setAmountRooms(count($rooms));

        $now = $criteria->getDate();

        /** @var Package[] $supposeAccommodations */
        $supposeAccommodations = $this->dm->getRepository('MBHPackageBundle:Package')
            ->findBy(
                [
                    'roomType.id'    => ['$in' => $roomTypeIDs],
                    'accommodations' => ['$exists' => false],
                    'isCheckOut'     => false,
                    'begin'          => ['$lte' => (clone $now)],
                ],
                [],
                $result->getTotal()[RoomTypeReportResult::TOTAL_ROOMS]
            );

        $supposeAccommodationTotal = count($supposeAccommodations);
        foreach($supposeAccommodations as $package) {
            $result->supposeAccommodations[$package->getRoomType()->getId()][] = $package;
        }


        foreach($rooms as $room) {
            /** @var Package $package */
            $package = $packageRepository->getPackageByAccommodation($room, $now);
            $roomStatus = $package ? $package->getRoomStatus() : Package::ROOM_STATUS_OPEN;

            if (!$criteria->status || $roomStatus === $criteria->status) {
                $roomTypeID = $room->getRoomType()->getId();
                $result->dataTable[$roomTypeID]['roomType'] = $room->getRoomType();
                $result->dataTable[$roomTypeID]['rooms'][] = $room;

                if($package) {
                    $result->packages[$room->getId()] = $package;
                    $result->plusForTourist(count($package->getTourists()));
                    $result->plusForGuests($package->getAdults() + $package->getChildren());

                    if($roomStatus == Package::ROOM_STATUS_OPEN) {
                        $result->plusOneForOpen();
                    } else {
                        $result->plusOneForReserve();
                    }
                } else {
                    if($supposeAccommodationTotal) {
                        $supposeAccommodationTotal--;
                        $result->plusOneForReserve();
                    }else {
                        $result->plusOneForOpen();
                    }
                }
            }
        }

        return $result;
    }
}