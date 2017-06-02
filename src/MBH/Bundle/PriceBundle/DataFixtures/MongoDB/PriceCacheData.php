<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\PriceCache;

/**
 * Class PriceCacheData

 */
class PriceCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'Основной тариф' => [
            'Двухместный' => [1500, 1000, 800],
            'Трехместный' => [2200, 1500, 1000],
        ],
        'Special tariff' => [
            'Двухместный' => [1000, 800, 500],
            'Трехместный' => [1200, 1000, 700],
        ],
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
            $tariffs  = $manager->getRepository('MBHPriceBundle:Tariff')->fetch($hotel);
            
            foreach ($tariffs as $tariff) {
                $data = self::DATA[$tariff->getFullTitle()][$roomType->getFullTitle()] ?? [1000, 500, 300];
                foreach ($period as $day) {
                    $cache = new PriceCache();
                    $cache->setRoomType($roomType)
                        ->setHotel($hotel)
                        ->setTariff($tariff)
                        ->setDate($day)
                        ->setPrice($data[0])
                        ->setAdditionalPrice($data[1])
                        ->setAdditionalChildrenPrice($data[2]);
                    $manager->persist($cache);
                    $manager->flush();
                }
            }
        }
    }

    public function getOrder()
    {
        return 30;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test'];
    }
}
