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
class AdditionalPriceCacheData extends AbstractFixture implements OrderedFixtureInterface
{

    public const PRICE_DATA = [
        'main-tariff' => [
            'roomType' => [
                'zero' => [
                    'price' => 1000,
                    'single' => null,
                    'additional' => null,
                    'isPersonPrice' => false,
                    'childPrice' => null,
                    'additionalChildPrice' => null,
                    'individualAdultPrices' => [],
                    'individualChildrenPrices' => [],
                ],
                'one' => [
                    'price' => 1000,
                    'single' => 900,
                    'additional' => 850,
                    'isPersonPrice' => false,
                    'childPrice' => null,
                    'additionalChildPrice' => null,
                    'individualAdultPrices' => [],
                    'individualChildrenPrices' => [],
                ],
                'two' => [
                    'price' => 2000,
                    'single' => 1900,
                    'additional' => 1500,
                    'isPersonPrice' => false,
                    'childPrice' => 1000,
                    'additionalChildPrice' => 900,
                    'individualAdultPrices' => [],
                    'individualChildrenPrices' => [],
                ],
                'three' => [
                    'price' => 2000,
                    'single' => 1900,
                    'additional' => 1500,
                    'isPersonPrice' => false,
                    'childPrice' => null,
                    'additionalChildPrice' => 700,
                    'individualAdultPrices' => [400, 300, 300],
                    'individualChildrenPrices' => [300, 200, 200],
                ],
                'four' => [
                    'price' => 2000,
                    'single' => 1900,
                    'additional' => 1500,
                    'isPersonPrice' => false,
                    'childPrice' => 800,
                    'additionalChildPrice' => 700,
                    'individualAdultPrices' => [400, 300],
                    'individualChildrenPrices' => [300, 150],
                ],
                'hostel' => [
                    'price' => 1000,
                    'single' => null,
                    'additional' => null,
                    'isPersonPrice' => false,
                    'childPrice' => null,
                    'additionalChildPrice' => null,
                    'individualAdultPrices' => [],
                    'individualChildrenPrices' => [],
                ],
            ]
        ]
    ];


    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight +2 days');
        $end = new \DateTime('midnight +1 month -2 days');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::PRICE_DATA as $tariffKey => $roomTypes) {
                /** @var Tariff $tariff */
                $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                foreach ($roomTypes['roomType'] as $roomTypeKey => $priceData) {
                    /** @var RoomType $roomType */
                    $roomType = $this->getReference($roomTypeKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $priceCache = new PriceCache();
                        $priceCache
                            ->setRoomType($roomType)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setDate($day)
                        ;
                        if (!$roomType->getIsHostel()) {
                            !$priceData['price'] === null ?: $priceCache->setPrice($priceData['price']);
                            !$priceData['single'] === null ?: $priceCache->setSinglePrice($priceData['single']);
                            !$priceData['additional'] === null ?: $priceCache->setAdditionalPrice($priceData['additional']);
                            !$priceData['isPersonPrice'] === null ?: $priceCache->setIsPersonPrice($priceData['isPersonPrice']);
                            !$priceData['childPrice'] === null ?: $priceCache->setChildPrice($priceData['childPrice']);
                            !$priceData['additionalChildPrice'] === null ?: $priceCache->setAdditionalChildrenPrice($priceData['additionalChildPrice']);
                            !$priceData['individualAdultPrices'] === null ?: $priceCache->setAdditionalPrices($priceData['individualAdultPrices']);
                            !$priceData['individualChildrenPrices'] === null ?: $priceCache->setAdditionalChildrenPrices($priceData['individualChildrenPrices']);
                        } else {
                            $priceCache->setPrice($priceData['price']);
                        }
                        $manager->persist($priceCache);
                    }

                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 560;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
