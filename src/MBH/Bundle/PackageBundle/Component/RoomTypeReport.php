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
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class RoomTypeReport
{
    const STATUS_OPEN = 'open';
    const STATUS_DEPT = 'dept';
    const STATUS_PAID = 'paid';
    const STATUS_NOT_OUT = 'not_out';
    const STATUS_OUT_NOW = 'out_now';
    const STATUS_WAIT = 'wait';

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

    public function getStatusByPackage(Package $package)
    {
        if (!$package->getOrder()) {
            return self::STATUS_OPEN;
        }

        if(!$package->getIsCheckIn()) {
            return self::STATUS_WAIT;
        }

        $now = new \DateTime('midnight');
        if (!$package->getIsCheckOut() && $now->format('Ymd') > $package->getEnd()->format('Ymd')) {
            return self::STATUS_NOT_OUT;
        }
        if ($package->getIsPaid()) {
            return $now->format('d.m.Y') == $package->getEnd()->format('d.m.Y') ?
                self::STATUS_OUT_NOW :
                self::STATUS_PAID;
        } else {
            return self::STATUS_DEPT;
        }
    }

    public function getAvailableStatues()
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_DEPT,
            self::STATUS_PAID,
            self::STATUS_NOT_OUT,
            self::STATUS_OUT_NOW,
            self::STATUS_WAIT,
        ];
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

        /** @var Room[] $rooms */
        $rooms = $queryBuilder->getQuery()->execute();

        $result = new RoomTypeReportResult();

        $result->total = [
            'rooms' => count($rooms),
            'open' => 0,
            'reserve' => 0,
            'guests' => 0,
        ];

        /** @var Package[] $supposeAccommodations */
        $supposeAccommodations = $this->dm->getRepository('MBHPackageBundle:Package')->findBy([
            'roomType.id' => ['$in' => $roomTypeIDs],
            'accommodation' => ['$exists' => false],
            'isCheckOut' => false
        ], [], $result->total['rooms']);

        $supposeAccommodationTotal = count($supposeAccommodations);
        foreach($supposeAccommodations as $package) {
            $result->supposeAccommodations[$package->getRoomType()->getId()][] = $package;
        }

        $now = new \DateTime('midnight');
        foreach($rooms as $room) {
            /** @var Package $package */
            $package = $packageRepository->getPackageByAccommodation($room, $now);
            $roomStatus = self::STATUS_OPEN;
            if($package) {
                $roomStatus = $this->getStatusByPackage($package);
            }

            if (!$criteria->status || $roomStatus === $criteria->status) {
                $roomTypeID = $room->getRoomType()->getId();
                $result->dataTable[$roomTypeID]['roomType'] = $room->getRoomType();
                $result->dataTable[$roomTypeID]['rooms'][] = $room;

                if($package) {
                    $result->packages[$room->getId()] = $package;
                    $result->total['guests'] += $package->getAdults() + $package->getChildren();

                    if($roomStatus == self::STATUS_OPEN) {
                        $result->total['open']++;
                    } else {
                        $result->total['reserve']++;
                    }
                } else {
                    if($supposeAccommodationTotal) {
                        $supposeAccommodationTotal--;
                        $result->total['reserve']++;
                    }else {
                        $result->total['open']++;
                    }
                }
            }
        }

        return $result;
    }
}