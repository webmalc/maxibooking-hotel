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
        if (!$package->getOrder() or !$package->getIsCheckIn()) {
            return self::STATUS_OPEN;
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

        $now = new \DateTime('midnight');
        foreach($rooms as $room) {
            /** @var Package $package */
            $package = $packageRepository->getPackageByAccommodation($room, $now);
            $packageStatus = $package ? $this->getStatusByPackage($package) : null;
            if($packageStatus == self::STATUS_OPEN || $package == null) {
                $result->total['open']++;
            } else {
                $result->total['reserve']++;
            }
            if (empty($criteria->status) || ($package === null && $criteria->status === self::STATUS_OPEN) ||
                ($package && $packageStatus === $criteria->status)
            ) {
                $result->dataTable[$room->getRoomType()->getId()]['roomType'] = $room->getRoomType();
                $result->dataTable[$room->getRoomType()->getId()]['rooms'][] = $room;

                if($package) {
                    $result->packages[$room->getId()] = $package;
                    $result->total['guests'] += $package->getAdults() + $package->getChildren();
                }
            }
        }

        return $result;
    }
}