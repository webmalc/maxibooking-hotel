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
     * @return int
     */
    public function recalculateByPackages(\DateTime $begin = null, \DateTime $end = null, array $roomTypes = [])
    {

        $begin = $begin ?: new \DateTime('midnight');
        $end = $end ?: new \DateTime('midnight +365 days');

        /** @var \MBH\Bundle\PriceBundle\Document\RoomCache[] $caches */
        $caches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
            $begin,
            $end,
            null,
            $roomTypes
        );

        $num = 0;
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

            if (($num % $batchSize) === 0) {
                $this->dm->flush();
                $this->dm->clear();
            }
        }
        $this->dm->flush();

        return $num;
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
                [
                    'command' => 'mbh:cache:recalculate',
                    'params' => [
                        '--roomTypes' => implode(',', $this->helper->toIds($roomTypes)),
                        '--begin' => $begin->format('d.m.Y'),
                        '--end' => $end->format('d.m.Y'),
                    ],

                ],
                $kernel
            );

            $this->container->get('old_sound_rabbit_mq.task_command_runner_producer')->publish(
                serialize(
                    $command
                )
            );
        }
    }
}
