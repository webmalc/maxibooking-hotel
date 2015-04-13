<?php

namespace MBH\Bundle\PriceBundle\Services;


use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \MBH\Bundle\PriceBundle\Document\RoomCache as RoomCacheDoc;


/**
 *  RoomCache service
 */
class RoomCache
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry 
     */
    protected $dm;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->helper = $this->container->get('mbh.helper');
    }

    /**
     * Create/update RoomCache docs
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param int $rooms
     * @param array $availableRoomTypes
     * @param array $weekdays
     */
    public function update(\DateTime $begin, \DateTime $end, Hotel $hotel, $rooms = 0, array $availableRoomTypes = [], array $weekdays = [])
    {
        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');
        $roomCaches = $updateCaches = $updates = [];

        (empty($availableRoomTypes)) ? $roomTypes = $hotel->getRoomTypes()->toArray() : $roomTypes = $availableRoomTypes;

        // find && group old caches
        $oldRoomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetchQueryBuilder($begin, $end, $hotel, $this->helper->toIds($roomTypes))
            ->getQuery()
            ->execute()
        ;
        foreach ($oldRoomCaches as $oldRoomCache) {

            if (!empty($weekdays) && !in_array($oldRoomCache->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldRoomCache->getDate()->format('d.m.Y')][$oldRoomCache->getRoomType()->getId()] = $oldRoomCache;
            $updates[] = [
                'criteria' => ['_id' => new \MongoId($oldRoomCache->getId())],
                'values' => [
                    'packagesCount' => $oldRoomCache->getPackagesCount(),
                    'totalRooms' => (int) $rooms,
                    'leftRooms' => (int) $rooms - $oldRoomCache->getPackagesCount()
                ]
            ];
        }

        foreach ($roomTypes as $roomType) {
            foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $endWithDay) as $date) {

                if (isset($updateCaches[$date->format('d.m.Y')][$roomType->getId()])) {
                    continue;
                }

                if (!empty($weekdays) && !in_array($date->format('w'), $weekdays)) {
                    continue;
                }

                $roomCaches[] = [
                    'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                    'roomType' => \MongoDBRef::create('RoomTypes', new \MongoId($roomType->getId())),
                    'date' => new \MongoDate($date->getTimestamp()),
                    'totalRooms' => (int) $rooms,
                    'packagesCount' => (int) 0,
                    'leftRooms' => (int) 0
                ];

            }
        }
        $this->container->get('mbh.mongo')->batchInsert('RoomCache', $roomCaches);
        $this->container->get('mbh.mongo')->update('RoomCache', $updates);
    }
}
