<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\CacheQueue;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\RoomCache;
use MBH\Bundle\PackageBundle\Document\RoomCacheTmp;
use MBH\Bundle\PackageBundle\Document\PriceCache;
use Symfony\Component\Process\Process;

/**
 *  RoomCacheGenerator service
 */
class RoomCacheGenerator
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
     * @var string 
     */
    protected $console;

    /**
     *
     * @var \MBH\Bundle\PackageBundle\Services\Calculation 
     */
    protected $calc;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->console = $container->get('kernel')->getRootDir() . '/../bin/console ';
        $this->calc = $container->get('mbh.calculation');
    }

    public function sendMessage()
    {
        $this->container->get('mbh.messenger')->send(
            $this->container->getParameter('mbh.room.cache.message'),
            'cache',
            $this->container->getParameter('mbh.room.cache.message.type')
        );
    }

    public function clearMessages()
    {
        $this->container->get('mbh.messenger')->clear('cache');
    }

    /**
     * @param bool $clear
     * @return bool
     */
    public function generateInBackground($clear = false)
    {
        $repo = $this->dm->getRepository('MBHPackageBundle:CacheQueue');
        
        if ($clear) {
            $repo->createQueryBuilder()->remove()->getQuery()->execute();
        }
        
        if ($repo->findOneBy(['status' => 'waiting', 'roomType' => null])) {
            return false;
        }

        $queue = new CacheQueue();
        $this->dm->persist($queue);
        $this->dm->flush();

        return true;
    }

    /**
     * @param RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return bool
     */
    public function generateForRoomTypeInBackground(RoomType $roomType, \DateTime $begin = null, \DateTime $end = null)
    {
        $repo = $this->dm->getRepository('MBHPackageBundle:CacheQueue');

        if ($repo->findOneBy(['status' => 'waiting', 'roomType' => null])) {
            return false;
        }
        if ($repo->findOneBy(['status' => 'waiting', 'roomType.id' => $roomType->getId(), 'begin' => $begin, 'end' => $end])) {
            return false;
        }

        $queue = new CacheQueue();
        $queue->setBegin($begin)->setEnd($end)->setRoomType($roomType);
        $this->dm->persist($queue);
        $this->dm->flush();

        return true;
    }

    /**
     * Generate prices for cache
     * @param string $roomTypeId
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $prices array[date('d.m.Y')][$roomTypeId][$tariffId] = array $prices
     * @param boolean $tmp use RoomCache collection or RoomCacheTmp
     */
    public function prices($roomTypeId = null, \DateTime $begin = null, \DateTime $end = null, array $prices = null, $tmp = false)
    {
        ($tmp) ? $cacheClassName = 'RoomCacheTmp' : $cacheClassName = 'RoomCache';

        $this->dm->clear();

        $qb = $this->dm->getRepository('MBHPackageBundle:' . $cacheClassName)->createQueryBuilder('q');

        if ($roomTypeId) {
            $qb->field('roomType.id')->equals($roomTypeId);
        }
        if ($begin) {
            $qb->field('date')->gte($begin);
        }
        if ($end) {
            $qb->field('date')->lte($end);
        }

        $caches = $qb->sort('date', 'asc')
                ->getQuery()
                ->execute()
        ;

        $cacheIds = [];

        foreach ($caches as $cache) {
            $cacheIds[] = $cache->getId();
        }

        $i = 0;
        foreach ($cacheIds as $id) {
            $cache = $this->dm->getRepository('MBHPackageBundle:' . $cacheClassName)->find($id);

            if (!$cache) {
                continue;
            }
            if($prices && isset($prices[$cache->getDate()->format('d.m.Y')][$cache->getRoomType()->getId()][$cache->getTariff()->getId()])) {
                $cache->setPrices($prices[$cache->getDate()->format('d.m.Y')][$cache->getRoomType()->getId()][$cache->getTariff()->getId()]);
            } else {
                $this->generatePrices($cache);
            }

            $this->dm->persist($cache);
            $i++;
            if ($i >= 200) {
                $this->dm->flush();
                $this->dm->clear();
                $i = 0;
            }
        }
        $this->dm->flush();
        $this->dm->clear();
    }

    public function copyTmpCache($tmpToCache = true)
    {
        if ($tmpToCache) {
            $from = 'RoomCacheTmp';
            $to = 'RoomCache';
        } else {
            $from = 'RoomCache';
            $to = 'RoomCacheTmp';
        }

        $this->dm->getDocumentCollection('MBHPackageBundle:' . $to)->drop();
        $this->dm->clear();

        $config = $this->container->getParameter('mbh.mongodb');
        $m = new \MongoClient($config['url']);
        $db = $m->$config['db'];

        $max = 100;
        $rounds = ceil($db->$from->count()/$max);

        for ($i = 1; $i <= $rounds; $i++) {
            $caches = $caches = $db->$from->find()->skip(($i - 1) * $max)->limit($max);

            if ($caches->count()) {
                $db->$to->batchInsert(iterator_to_array($caches));
            }

            unset($caches);
        }

        return true;
    }

    /**
     * Generate RoomCache for all hotels & roomTypes
     * @return int
     */
    public function generate()
    {
        $total = 0;

        $this->sendMessage();

        // Remove all old RoomCache
        $this->dm->getDocumentCollection('MBHPackageBundle:RoomCacheTmp')->drop();

        // Iterate hotels & tariffs
        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {

            if (!$hotel->getSaleDays()) {
                continue;
            }
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                    ->createQueryBuilder('q')
                    ->field('hotel.id')->equals($hotel->getId())
                    ->sort('isDefault', 'desc')
                    ->sort('begin', 'asc')
                    ->getQuery()
                    ->execute()
            ;

            foreach ($tariffs as $tariff) {
                $total += $this->generateForTariff($tariff, null, null, null, true);
            }
        }
        $this->prices(null, null, null, null, true);

        $this->copyTmpCache();

        $this->container->get('mbh.channelmanager')->update();

        $this->clearMessages();

        return $total;
    }

    /**
     * Generate RoomCache for all hotels & roomTypes
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array $prices array[date('d.m.Y')][$roomTypeId][$tariffId] = array $prices
     * @param boolean $tmp use RoomCache collection or RoomCacheTmp
     * @return int
     */
    public function generateForRoomType(RoomType $roomType, \DateTime $begin = null, \DateTime $end = null, array $prices = null, $tmp = true)
    {
        $total = 0;

        ($tmp) ? $cacheClassName = 'RoomCacheTmp' : $cacheClassName = 'RoomCache';
        $this->sendMessage();

        if ($tmp) {
            $this->copyTmpCache(false);
        }

        //Remove all old RoomCache with same $roomType
        $qb = $this->dm->getRepository('MBHPackageBundle:' . $cacheClassName)
                        ->createQueryBuilder('q')
                        ->remove()
                        ->field('roomType.id')->equals($roomType->getId())
        ;
        if ($begin) {
            $qb->field('date')->gte($begin);
        }
        if ($end) {
            $qb->field('date')->lte($end);
        }

        $qb->getQuery()->execute();

        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->createQueryBuilder('q')
            ->field('hotel.id')->equals($roomType->getHotel()->getId())
            ->sort('isDefault', 'desc')
            ->sort('begin', 'asc')
            ->getQuery()
            ->execute()
        ;
        foreach ($tariffs as $tariff) {
            $total += $this->generateForTariff($tariff, $roomType, $begin, $end, $tmp);
        }

        $this->prices($roomType->getId(), $begin, $end, $prices, $tmp);

        if ($tmp) {
            $this->copyTmpCache();
        }

        $this->updateChannelManagerInBackground($roomType, null, null);
        $this->clearMessages();
        return $total;
    }

    /**
     * Generate RoomCache for all hotels & roomTypes
     * 
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $calcRoomType
     * @param \DateTime $calcBegin
     * @param \DateTime $calcEnd
     * @param boolean $tmp use RoomCache collection or RoomCacheTmp
     * @return int
     */
    public function generateForTariff(Tariff $tariff, RoomType $calcRoomType = null, \DateTime $calcBegin = null, \DateTime $calcEnd = null, $tmp = false)
    {
        (empty($tariff->getWeekDays())) ? $weekDays = [] : $weekDays = $tariff->getWeekDays();
        $total = 0;
        $begin = new \DateTime();
        $begin->setTime(0, 0, 0);
        $saleDate = clone $begin;
        $saleDate->modify('+' . $tariff->getHotel()->getSaleDays() . ' day');
        $end = clone $tariff->getEnd();


        //Calculate begin date
        $begin->modify($this->container->getParameter('mbh.room.cache.date.modify'));
        ($tariff->getBegin() < $begin) ? $begin : $begin = clone $tariff->getBegin();
        ($calcBegin && $begin < $calcBegin) ? $begin = clone $calcBegin : $begin;

        //Calculate end date
        ($end <= $saleDate) ? $end : $end = clone $saleDate;
        ($calcEnd && $end > $calcEnd) ? $end = clone $calcEnd : $end;

        // roomCacheOverwrite
        $roomCacheOverwrite = $this->dm->getRepository('MBHPackageBundle:RoomCacheOverwrite')->findStructured();

        $end->modify('+1 day');

        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $date) {

            $date->setTime(0,0,0);

            if(!empty($weekDays) && !in_array($date->format('N'), $weekDays)) {
                continue;
            }

            foreach ($tariff->getHotel()->getRoomTypes() as $roomType) {


                $overwrite = null;
                if (isset($roomCacheOverwrite[$tariff->getId()][$roomType->getId()][$date->format('d.m.Y')])) {
                    $overwrite = $roomCacheOverwrite[$tariff->getId()][$roomType->getId()][$date->format('d.m.Y')];
                }

                //skip disabled roomTypes
                if (!$roomType->getIsEnabled()) {
                    continue;
                }

                if ($calcRoomType && $calcRoomType->getId() != $roomType->getId()) {
                    continue;
                }

                //Create cache
                $rooms = $this->countRooms($tariff, $roomType, $overwrite);
                $cache = ($tmp) ? new RoomCacheTmp() : new RoomCache();
                $cache->setTariff($tariff)
                        ->setRoomType($roomType)
                        ->setDate($date)
                        ->setIsDefault($tariff->getIsDefault())
                        ->setIsOnline($tariff->getIsOnline())
                        ->setPlaces($roomType->getTotalPlaces())
                        ->setTotalRooms($rooms)
                        ->setRooms($this->countPackages($tariff, $roomType, $date, $rooms))
                ;

                $this->dm->persist($cache);
                $total++;
            }
        }
        $this->dm->flush();

        return $total;
    }

    /**
     * Generate array of prices for cache
     * @param \MBH\Bundle\PackageBundle\Document\RoomCache $cache
     * @return \MBH\Bundle\PackageBundle\Document\RoomCache
     */
    private function generatePrices(RoomCache $cache)
    {
        $roomType = $cache->getRoomType();
        $tariff = $cache->getTariff();
        $date = $cache->getDate();
        $overwrite = $this->dm->getRepository('MBHPackageBundle:RoomCacheOverwrite')
             ->findOneBy([
                 'tariff.id' => $tariff->getId(),
                 'roomType.id' => $roomType->getId(),
                 'date' => $date,
                 'isEnabled' => true
             ]);

        foreach ($roomType->getAdultsChildrenCombinations() as $comb) {
            $price = new PriceCache();
            $price->setAdults($comb['adults'])
                ->setChildren($comb['children'])
                ->setPrice($this->calc->getDayPrice(
                    $tariff->getId(),
                    $roomType->getId(),
                    $date,
                    $comb['adults'],
                    $comb['children'],
                    $overwrite
                ))
            ;
            $cache->addPrice($price);
        }
        return $cache;
    }

    /**
     * Count rooms for tariff
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite $overwrite
     * @return int
     */
    private function countRooms(Tariff $tariff, RoomType $roomType, RoomCacheOverwrite $overwrite = null)
    {
        $rooms = 0;

        foreach ($roomType->getRooms() as $room) {
            if ($room->getIsEnabled()) {
                $rooms++;
            }
        }

        if ($overwrite && is_numeric($overwrite->getPlaces()) && $overwrite->getRoomType()->getId() == $roomType->getId() && $rooms > $overwrite->getPlaces()) {
            return $overwrite->getPlaces();
        }

        foreach ($tariff->getRoomQuotas() as $quota) {
            if ($quota->getNumber() !== null && $quota->getRoomType()->getId() == $roomType->getId() && $rooms > $quota->getNumber()) {
                $rooms = $quota->getNumber();
            }
        }

        return $rooms;
    }

    /**
     * Count packages for date
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $date
     * @param int $rooms
     * @return int
     */
    private function countPackages(Tariff $tariff, RoomType $roomType, \DateTime $date, $rooms)
    {
        $allPackages = $this->dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('p')
                ->field('roomType.id')->equals($roomType->getId())
                ->field('begin')->lte($date)
                ->field('end')->gt($date)
                ->getQuery()
                ->count()
        ;
        $tariffPackages = $this->dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('t')
                ->field('tariff.id')->equals($tariff->getId())
                ->field('roomType.id')->equals($roomType->getId())
                ->field('begin')->lte($date)
                ->field('end')->gt($date)
                ->getQuery()
                ->count()
        ;

        if ($tariff->getIsDefault()) {
            $roomsDefault = $rooms;
        } else {
            $defaultCache = $caches = $this->dm->getRepository('MBHPackageBundle:RoomCache')
                ->createQueryBuilder('q')
                ->field('isDefault')->equals(true)
                ->field('date')->gte($date)
                ->field('roomType.id')->equals($roomType->getId())
                ->limit(1)
                ->getQuery()
                ->getSingleResult();
            ;

            if (!empty($defaultCache)) {
                $roomsDefault = $defaultCache->getTotalRooms();
            } else {
                $roomsDefault = $roomType->getRooms()->count();
            }
        }

        $totalAll = $roomsDefault - $allPackages;
        $totalTariff = $rooms - $tariffPackages;

        ($totalTariff > $totalAll) ? $total = $totalAll : $total = $totalTariff;

        return $total;
    }

    /**
     * Recalculate RoomCache places without prices
     * @param RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function recalculateCache(RoomType $roomType, \DateTime $begin, \DateTime $end)
    {

        $caches = $this->dm->getRepository('MBHPackageBundle:RoomCache')
            ->createQueryBuilder('q')
            ->field('roomType.id')->equals($roomType->getId())
            ->field('date')->gte($begin)
            ->field('date')->lt($end)
            ->getQuery()
            ->execute();

        $prices = [];
        foreach ($caches as $cache) {
            $prices[$cache->getDate()->format('d.m.Y')][$cache->getRoomType()->getId()][$cache->getTariff()->getId()] = $cache->getPrices();
        }

        $this->generateForRoomType($roomType, $begin, $end, $prices, false);
    }

    public function updateChannelManagerInBackground(RoomType $roomType = null, \DateTime $begin = null, \DateTime $end = null)
    {
        ($begin) ? $beginStr = ' --begin=' . $begin->format('d.m.Y') : $beginStr = '';
        ($end) ? $endStr = ' --end=' . $end->format('d.m.Y') : $endStr = '';
        ($roomType) ? $roomTypeStr = ' --roomType=' . $roomType->getId() : $roomTypeStr = '';
        ($this->container->get('kernel')->getEnvironment() == 'prod') ? $env = '--env=prod ' : $env = '';

        $process = new Process(
            'nohup php ' . $this->console . 'mbh:channelmanager:update  ' . $roomTypeStr
             . $beginStr . $endStr . ' ' . $env . '> /dev/null 2>&1 &'
        );
        $process->run();
    }
}
