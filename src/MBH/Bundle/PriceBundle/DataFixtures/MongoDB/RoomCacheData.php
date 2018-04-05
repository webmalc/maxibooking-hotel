<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\RoomCache;

/**
 * Class RoomCacheData

 */
class RoomCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'single' => 10,
        'roomtype-double' => 20,
        'hotel-triple' => 5,
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +6 month');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();

        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::DATA as $roomKey => $numberOfRooms) {
                /** @var RoomType $roomType */
                $roomType = $this->getReference($roomKey . '/' . $hotelNumber);
                foreach ($period as $day) {
                    $cache = new RoomCache();
                    $cache->setRoomType($roomType)
                        ->setHotel($hotel)
                        ->setDate($day)
                        ->setTotalRooms($numberOfRooms);
                    $manager->persist($cache);
                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'sandbox'];
    }
}
