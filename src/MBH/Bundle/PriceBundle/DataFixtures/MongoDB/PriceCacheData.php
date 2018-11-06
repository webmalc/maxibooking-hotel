<?php
namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\PriceCache;

/**
 * Class PriceCacheData
 */
class PriceCacheData extends AbstractFixture implements OrderedFixtureInterface
{
    const PERIOD_LENGTH_STR = 'midnight +6 month';
    const DATA = [
        'main-tariff' => [
            'single' => ['ru' => [1200, 900, 700], 'com' => [30, 20, 18]],
            'roomtype-double' => ['ru' => [1500, 1000, 800], 'com' => [35, 27, 20]],
            'hotel-triple' => ['ru' => [2200, 1500, 1000], 'com' => [50, 40, 25]],
        ],
        'special-tariff' => [
            'single' => ['ru' => [1200, 900, 700], 'com' => [30, 20, 18]],
            'roomtype-double' => ['ru' => [1000, 800, 500], 'com' => [35, 27, 20]],
            'hotel-triple' => ['ru' => [1200, 1000, 700], 'com' => [50, 40, 25]],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime(self::PERIOD_LENGTH_STR);
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $localeType = $this->container->getParameter('locale') === 'ru' ? 'ru' : 'com';
        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::DATA as $tariffKey => $tariffPricesData) {
                /** @var Tariff $tariff */
                $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                foreach ($tariffPricesData as $roomKey => $roomPricesData) {
                    /** @var RoomType $roomType */
                    $roomType = $this->getReference($roomKey . '/' . $hotelNumber);
                    $priceData = $roomPricesData[$localeType];
                    foreach ($period as $day) {
                        $cache = new PriceCache();
                        $cache->setRoomType($roomType)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setDate($day)
                            ->setPrice($priceData[0])
                            ->setAdditionalPrice($priceData[1])
                            ->setAdditionalChildrenPrice($priceData[2]);
                        $manager->persist($cache);
                    }
                }
            }
        }

        $manager->flush();
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
        return ['test', 'dev', 'sandbox'];
    }
}
