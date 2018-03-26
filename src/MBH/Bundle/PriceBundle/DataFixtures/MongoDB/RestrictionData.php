<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use MBH\Bundle\PriceBundle\Document\Restriction;

/**
 * Class RestrictionData

 */
class RestrictionData extends AbstractFixture implements OrderedFixtureInterface
{

    const DATA = [
        'Основной тариф' => [
            'Двухместный' => 3,
            'Трехместный' => 2,
        ],
        'Special tariff' => [
            'Двухместный' => 5,
            'Трехместный' => 6,
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
                $data = self::DATA[$tariff->getFullTitle()][$roomType->getFullTitle()] ?? null;
                if (!$data) {
                    continue;
                }
                foreach ($period as $day) {
                    $cache = new Restriction();
                    $cache->setRoomType($roomType)
                        ->setHotel($hotel)
                        ->setTariff($tariff)
                        ->setMinStay($data)
                        ->setDate($day);
                    $manager->persist($cache);
                    $manager->flush();
                }
            }
        }
    }

    public function getOrder()
    {
        return 40;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
