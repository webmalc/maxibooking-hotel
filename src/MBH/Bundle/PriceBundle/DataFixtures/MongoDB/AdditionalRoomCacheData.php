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
class AdditionalRoomCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'one' => 6,
        'two' => 4,
        'three' => 5,
        'four' => 20,
        'hostel' => 3,
        'zero' => 6
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $begin = new \DateTime('midnight +2 days');
        $end = new \DateTime('midnight +1 month -2 days');
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
        return 510;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
