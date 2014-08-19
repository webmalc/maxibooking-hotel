<?php

namespace MBH\Bundle\PackageBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\RoomCache;
use MBH\Bundle\PackageBundle\Document\PriceCache;
use MBH\Bundle\BaseBundle\Document\Message;
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
        $this->clearMessages();
        $message = new Message();
        $message->setFrom('cache')
                ->setText($this->container->getParameter('mbh.room.cache.message'))
                ->setType($this->container->getParameter('mbh.room.cache.message.type'))
        ;
        $this->dm->persist($message);
        $this->dm->flush();
    }

    public function clearMessages()
    {
        //Remove all old RoomCache with same $roomType
        $this->dm->getRepository('MBHBaseBundle:Message')
                ->createQueryBuilder('q')
                ->remove()
                ->field('from')->equals('cache')
                ->getQuery()
                ->execute()
        ;
    }

    /**
     * Run generate as command 
     */
    public function generateInBackground()
    {
        ($this->container->get('kernel')->getEnvironment() == 'prod') ? $env = '--env=prod ' : $env = '';

        $process = new Process('nohup php ' . $this->console . 'mbh:cache:generate --no-debug ' . $env . '> /dev/null 2>&1 &');
        $process->run();
    }

    /**
     * Run generateForRoomType as command 
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function generateForRoomTypeInBackground(RoomType $roomType, \DateTime $begin = null, \DateTime $end = null)
    {
        ($begin) ? $beginStr = ' --begin=' . $begin->format('d.m.Y') : $beginStr = '';
        ($end) ? $endStr = ' --end=' . $end->format('d.m.Y') : $endStr = '';
        ($this->container->get('kernel')->getEnvironment() == 'prod') ? $env = '--env=prod ' : $env = '';

        $process = new Process(
                'nohup php ' . $this->console . 'mbh:cache:generate  --no-debug --roomType=' .
                $roomType->getId() . $beginStr . $endStr . ' ' . $env . '> /dev/null 2>&1 &'
        );
        $process->run();
    }

    /**
     * Generate prices for cache
     * @param string $roomTypeId
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function prices($roomTypeId = null, \DateTime $begin = null, \DateTime $end = null)
    {
        $this->dm->clear();

        $qb = $this->dm->getRepository('MBHPackageBundle:RoomCache')->createQueryBuilder('q');

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
        foreach ($caches as $cache) {
            $cacheIds[] = $cache->getId();
        }

        $i = 0;
        foreach ($cacheIds as $id) {
            $cache = $this->dm->getRepository('MBHPackageBundle:RoomCache')->find($id);

            if (!$cache) {
                continue;
            }
            $this->generatePrices($cache);
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

    /**
     * Generate RoomCache for all hotels & roomTypes
     * @return int
     */
    public function generate()
    {
        $total = 0;

        $this->sendMessage();

        // Remove all old RoomCache
        $this->dm->getDocumentCollection('MBHPackageBundle:RoomCache')->drop();

        // Iterate hotels & tariffs
        foreach ($this->dm->getRepository('MBHHotelBundle:Hotel')->findAll() as $hotel) {

            if (!$hotel->getSaleDays()) {
                continue;
            }
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                    ->createQueryBuilder('q')
                    ->field('hotel.id')->equals($hotel->getId())
                    ->sort('begin', 'asc')
                    ->getQuery()
                    ->execute()
            ;

            foreach ($tariffs as $tariff) {
                $total += $this->generateForTariff($tariff);
            }
        }
        $this->prices();

        $this->container->get('mbh.channelmanager')->update();

        $this->clearMessages();

        return $total;
    }

    /**
     * Generate RoomCache for all hotels & roomTypes
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     * @return int
     */
    public function generateForRoomType(RoomType $roomType, \DateTime $begin = null, \DateTime $end = null)
    {
        $total = 0;

        $this->sendMessage();

        //Remove all old RoomCache with same $roomType
        $qb = $this->dm->getRepository('MBHPackageBundle:RoomCache')
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

        foreach ($roomType->getHotel()->getTariffs() as $tariff) {
            $total += $this->generateForTariff($tariff, $roomType, $begin, $end);
        }

        $this->prices($roomType->getId(), $begin, $end);
        $this->container->get('mbh.channelmanager')->update(null, null, $roomType);
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
     * @return int
     */
    public function generateForTariff(Tariff $tariff, RoomType $calcRoomType = null, \DateTime $calcBegin = null, \DateTime $calcEnd = null)
    {
        $total = 0;
        $begin = new \DateTime();
        $begin->setTime(0, 0, 0);
        $saleDate = clone $begin;
        $saleDate->modify('+' . $tariff->getHotel()->getSaleDays() . ' day');
        $end = clone $tariff->getEnd();


        //Calculate begin date
        ($tariff->getBegin() < $begin) ? $begin : $begin = clone $tariff->getBegin();
        ($calcBegin && $begin < $calcBegin) ? $begin = clone $calcBegin : $begin;

        //Calculate end date
        ($end <= $saleDate) ? $end : $end = clone $saleDate;
        ($calcEnd && $end > $calcEnd) ? $end = clone $calcEnd : $end;

        $end->modify('+1 day');

        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), $end) as $date) {

            foreach ($tariff->getHotel()->getRoomTypes() as $roomType) {

                if ($calcRoomType && $calcRoomType->getId() != $roomType->getId()) {
                    continue;
                }

                //Create cache
                $rooms = $this->countRooms($tariff, $roomType);
                $cache = new RoomCache();
                $cache->setTariff($tariff)
                        ->setRoomType($roomType)
                        ->setDate($date)
                        ->setIsDefault($tariff->getIsDefault())
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
        foreach ($cache->getRoomType()->getAdultsChildrenCombinations() as $comb) {
            foreach ($cache->getRoomType()->getHotel()->getFood() as $food) {

                $price = new PriceCache();
                $price->setAdults($comb['adults'])
                        ->setChildren($comb['children'])
                        ->setFood($food)
                        ->setPrice($this->calc->getDayPrice(
                                        $cache->getTariff()->getId(), $cache->getRoomType()->getId(), $cache->getDate(), $comb['adults'], $comb['children'], $food
                        ))
                ;
                $cache->addPrice($price);
            }
        }
        return $cache;
    }

    /**
     * Count rooms for tariff
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return int
     */
    private function countRooms(Tariff $tariff, RoomType $roomType)
    {
        $rooms = $roomType->getRooms()->count();

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
        $all = $this->dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('p')
                ->field('roomType.id')->equals($roomType->getId())
                ->field('begin')->lte($date)
                ->field('end')->gt($date)
                ->getQuery()
                ->count()
        ;
        $tariff = $this->dm->getRepository('MBHPackageBundle:Package')
                ->createQueryBuilder('t')
                ->field('tariff.id')->equals($tariff->getId())
                ->field('roomType.id')->equals($roomType->getId())
                ->field('begin')->lte($date)
                ->field('end')->gt($date)
                ->getQuery()
                ->count()
        ;

        $totalAll = $rooms - $all;
        $totalTariff = $rooms - $tariff;

        ($totalTariff > $totalAll) ? $total = $totalAll : $total = $totalTariff;

        return $total;
    }

    /**
     * Decrease rooms count
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function decrease(RoomType $roomType, \DateTime $begin, \DateTime $end)
    {
        $caches = $this->dm->getRepository('MBHPackageBundle:RoomCache')
                           ->createQueryBuilder('q')
                           ->field('roomType.id')->equals($roomType->getId())
                           ->field('date')->gte($begin)
                           ->field('date')->lt($end)
                           ->getQuery()
                           ->execute();
        
        foreach ($caches as $cache) {
            $cache->setRooms($cache->getRooms() - 1);
            $this->dm->persist($cache);
        }
        $this->dm->flush();
        $this->updateChannelManagerInBackground($roomType, $begin, $end);
    }
    
    /**
     * Increase rooms count
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function increase(RoomType $roomType, \DateTime $begin, \DateTime $end)
    {
        $caches = $this->dm->getRepository('MBHPackageBundle:RoomCache')
                           ->createQueryBuilder('q')
                           ->field('roomType.id')->equals($roomType->getId())
                           ->field('date')->gte($begin)
                           ->field('date')->lt($end)
                           ->getQuery()
                           ->execute();
        
        foreach ($caches as $cache) {
            $cache->setRooms($cache->getRooms() + 1);
            if ($cache->getRooms() > $cache->getTotalRooms()) {
                $cache->setRooms($cache->getTotalRooms());
            }
            $this->dm->persist($cache);
        }
        $this->dm->flush();

        $this->updateChannelManagerInBackground($roomType, $begin, $end);
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
