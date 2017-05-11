<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\RoomCache;

/**
 * Class RoomCacheData

 */
class RoomCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'Двухместный' => 20,
        'Трехместный' => 5,
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        
        $roomTypes = $manager->getRepository('MBHHotelBundle:RoomType')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +15 days');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        foreach ($roomTypes as $roomType) {
            $hotel = $roomType->getHotel();
            foreach ($period as $day) {
                $cache = new RoomCache();
                $cache->setRoomType($roomType)
                    ->setHotel($hotel)
                    ->setDate($day)
                    ->setTotalRooms(self::DATA[$roomType->getFullTitle()] ?? 10);
                $manager->persist($cache);
                $manager->flush();
            }
        }
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
        return ['test'];
    }
}
