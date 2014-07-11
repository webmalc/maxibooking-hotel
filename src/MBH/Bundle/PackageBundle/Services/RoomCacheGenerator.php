<?php

namespace MBH\Bundle\PackageBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\RoomCache;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->console = $container->get('kernel')->getRootDir() . '/../bin/console ';
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
        $process = new Process('nohup php '. $this->console .'mbh:cache:generate > /dev/null 2>&1 &');
        $process->run();
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
        $this->clearMessages();
        return $total;
    }

    /**
     * Generate RoomCache for all hotels & roomTypes
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
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
                        ->setTotalRooms($rooms)
                        ->setRooms($rooms - $this->countPackages($tariff, $roomType, $date));
                ;

                $this->dm->persist($cache);
                $total++;
            }
        }
        $this->dm->flush();
        return $total;
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
     * @return int
     */
    private function countPackages(Tariff $tariff, RoomType $roomType, \DateTime $date)
    {
        return $this->dm->getRepository('MBHPackageBundle:Package')
                        ->createQueryBuilder('q')
                        ->field('tariff.id')->equals($tariff->getId())
                        ->field('roomType.id')->equals($roomType->getId())
                        ->field('begin')->lte($date)
                        ->field('end')->gte($date)
                        ->getQuery()
                        ->count()
        ;
    }

}
