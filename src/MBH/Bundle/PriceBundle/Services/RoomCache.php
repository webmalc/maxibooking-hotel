<?php

namespace MBH\Bundle\PriceBundle\Services;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PackageInfo;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Lib\Task\Command;
use Symfony\Component\Process\Process;

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
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param bool $decrease
     */
    public function recalculate(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff, $decrease = true)
    {
        $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin,
            $end,
            $roomType->getHotel(),
            [$roomType->getId()]
        );

        foreach ($roomCaches as $roomCache) {
            if (empty($roomCache->getTariff()) || $roomCache->getTariff()->getId() == $tariff->getId()) {
                if ($decrease) {
                    $roomCache->addPackage()->soldRefund($tariff);
                } else {
                    $roomCache->removePackage()->soldRefund($tariff, true);
                }
                $this->dm->persist($roomCache);
            }
        }
        $this->dm->flush();
    }

    /**
     * @param \DateTime $begin |null
     * @param \DateTime $end |null
     * @param array $roomTypes array of ids
     * @return array
     */
    public function recalculateByPackages(\DateTime $begin = null, \DateTime $end = null, array $roomTypes = [])
    {
        $logger = $this->container->get('mbh.room_cache.logger');
        $logDateFormat = 'd.m.Y';
        $logTimeFormat = 'H:i:s';

        $begin = $begin ?: new \DateTime('midnight');
        $end = $end ?: new \DateTime('midnight +365 days');

        $logger->info('Room caches recalculation starts at ' . date($logTimeFormat)
            . '. Parameters of command: '
            . ' from ' . $begin->format($logDateFormat)
            . ' to ' . $end->format($logDateFormat)
            . ' for room types with ids: [' . implode(', ', $roomTypes) . ']');

        /** @var \MBH\Bundle\PriceBundle\Document\RoomCache[] $caches */
        $caches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin,
            $end,
            null,
            $roomTypes
        );

        $num = 0;
        $numberOfInconsistencies = 0;
        $batchSize = 3;

        foreach ($caches as $cache) {
            $qb = $this->dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder()
                ->field('begin')->lte($cache->getDate())
                ->field('end')->gt($cache->getDate())
                ->field('deletedAt')->equals(null)
                ->field('roomType.id')->equals($cache->getRoomType()->getId());
            if ($cache->getTariff()) {
                $qb->field('tariff.id')->equals($cache->getTariff()->getId());
            }

            $total = $qb->getQuery()->count();

            if ($total != $cache->getPackagesCount()) {
                $cache->setPackagesCount($total);
            }

            if (!count($cache->getPackageInfo()) && $total) {
                $packages = $qb->getQuery()->execute();
                foreach ($packages as $package) {
                    $tariff = $package->getTariff();
                    $cache->soldRefund($tariff);
                }
            }

            $this->dm->persist($cache);
            $num += 1;

            $oldLeftRoomsValue = $cache->getLeftRooms();
            $cache->calcLeftRooms();

            $cacheLogMessage = 'Recalculated room cache for hotel "'
                . $cache->getHotel()->getName() . '" (ID="' . $cache->getHotel()->getId() . '")'
                . ' room type "' . $cache->getRoomType()->getName() . '(ID="' . $cache->getRoomType()->getId() . '"),'
                . ' date ' . $cache->getDate()->format($logDateFormat) . ','
                . ' old value: ' . $oldLeftRoomsValue . ','
                . ' calculated value: ' . $cache->getLeftRooms();

            if ($oldLeftRoomsValue != $cache->getLeftRooms()) {
                $logger->error($cacheLogMessage);
                $numberOfInconsistencies++;
            } else {
                $logger->info($cacheLogMessage);
            }

            if (($num % $batchSize) === 0) {
                $this->dm->flush();
                $this->dm->clear();
            }
        }
        $this->dm->flush();

        $afterMessage = 'Room caches recalculation ends at ' . date($logTimeFormat)
            . '. Parameters of command:'
            . ' from ' . $begin->format($logDateFormat)
            . ' to ' . $end->format($logDateFormat)
            . ' for room types with ids: [' . implode(', ', $roomTypes) . ']'
            . ' ' . $num . ' caches handled';

        if ($numberOfInconsistencies > 0) {
            $logger->error($afterMessage);
            $logger->error('Number of inconsistencies:' . $numberOfInconsistencies);
        } else {
            $logger->info($afterMessage);
            $logger->info('OK. Inconsistencies not found');
        }

        return ['total' => $num, 'numberOfInconsistencies' => $numberOfInconsistencies];
    }


    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param Hotel $hotel
     * @param int $rooms
     * @param bool $isClosed
     * @param array $availableRoomTypes
     * @param array $tariffs
     * @param array $weekdays
     */
    public function update(
        \DateTime $begin,
        \DateTime $end,
        Hotel $hotel,
        $rooms = 0,
        $isClosed = false,
        array $availableRoomTypes = [],
        array $tariffs = [],
        array $weekdays = []
    ) {
        $endWithDay = clone $end;
        $endWithDay->modify('+1 day');
        $roomCaches = $updateCaches = $updates = $remove = [];

        (empty($availableRoomTypes)) ? $roomTypes = $hotel->getRoomTypes()->toArray(
        ) : $roomTypes = $availableRoomTypes;

        $loggerMessage = 'Begin update of room caches with parameters:'
            . ' begin: ' . $begin->format('d.m.Y')
            . ', end: ' . $end->format('d.m.Y')
            . ', hotel ID: ' . $hotel->getId()
            . ', number of rooms: ' . $rooms
            . ', is closed: ' . ($isClosed ? 'true' : 'false')
            . ', available room type: ' . join(', ', $this->helper->toIds($availableRoomTypes))
            . ', tariffs: ' . join(', ', $this->helper->toIds($tariffs))
            . ', available room type: ' . join(', ', $weekdays)
        ;

        $this->container->get('logger')->addAlert($loggerMessage);

        // find && group old caches
        $oldRoomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')
            ->fetch(
                $begin,
                $end,
                $hotel,
                $this->helper->toIds($roomTypes),
                empty($tariffs) ? null : $this->helper->toIds($tariffs)
            );

        foreach ($oldRoomCaches as $oldRoomCache) {
            if (!empty($weekdays) && !in_array($oldRoomCache->getDate()->format('w'), $weekdays)) {
                continue;
            }

            $updateCaches[$oldRoomCache->getTariff() ? $oldRoomCache->getTariff()->getId() : 0][$oldRoomCache->getDate(
            )->format('d.m.Y')][$oldRoomCache->getRoomType()->getId()] = $oldRoomCache;

            if ($rooms == -1) {
                if ($oldRoomCache->getPackagesCount() <= 0) {
                    $remove['_id']['$in'][] = new \MongoId($oldRoomCache->getId());
                }
                continue;
            }

            $updates[] = [
                'criteria' => ['_id' => new \MongoId($oldRoomCache->getId())],
                'values' => [
                    'packagesCount' => $oldRoomCache->getPackagesCount(),
                    'totalRooms' => (int)$rooms,
                    'leftRooms' => (int)$rooms - $oldRoomCache->getPackagesCount(),
                    'isClosed' => $isClosed,
                ],
            ];
        }

        (empty($tariffs)) ? $tariffs = [0] : $tariffs;

        foreach ($tariffs as $tariff) {
            foreach ($roomTypes as $roomType) {
                foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $endWithDay) as $date) {
                    if (isset(
                        $updateCaches[$tariff ? $tariff->getId() : 0][$date->format('d.m.Y')][$roomType->getId()]
                    )) {
                        continue;
                    }

                    if (!empty($weekdays) && !in_array($date->format('w'), $weekdays)) {
                        continue;
                    }

                    $roomCaches[] = [
                        'hotel' => \MongoDBRef::create('Hotels', new \MongoId($hotel->getId())),
                        'roomType' => \MongoDBRef::create('RoomTypes', new \MongoId($roomType->getId())),
                        'tariff' => $tariff ? \MongoDBRef::create('Tariffs', new \MongoId($tariff->getId())) : null,
                        'date' => new \MongoDate($date->getTimestamp()),
                        'totalRooms' => (int)$rooms,
                        'packagesCount' => (int)0,
                        'leftRooms' => (int)$rooms,
                        'isEnabled' => true,
                        'isClosed' => $isClosed,
                    ];
                }
            }
        }

        if ($rooms == -1) {
            $this->container->get('mbh.mongo')->remove('RoomCache', $remove);
        } else {
            $this->container->get('mbh.mongo')->batchInsert('RoomCache', $roomCaches);
            $this->container->get('mbh.mongo')->update('RoomCache', $updates);
            /** @var \AppKernel $kernel */
            $kernel = $this->container->get('kernel');
            $command = new Command(
                'mbh:cache:recalculate',
                [
                    '--roomTypes' => implode(',', $this->helper->toIds($roomTypes)),
                    '--begin' => $begin->format('d.m.Y'),
                    '--end' => $end->format('d.m.Y'),
                ],
                $kernel->getClient(),
                $kernel->getEnvironment(),
                $kernel->isDebug()
            );

            $this->container->get('old_sound_rabbit_mq.task_cache_recalculate_producer')->publish(
                serialize(
                    $command
                )
            );
        }
    }
}
