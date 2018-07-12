<?php

namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\RoomTypeCategoryData;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\PriceCache;

/**
 * Class PriceCacheData
 */
class AdditionalPriceCacheCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    public const TARIFFS = [
        'main-tariff' => [
            'beginOffset' => 0,
            'endOffset' => 30,
        ],
        AdditionalTariffData::DOWN_TARIFF_NAME.'-tariff'=> [
            'beginOffset' => 4,
            'endOffset' => 15,
        ],
        AdditionalTariffData::UP_TARIFF_NAME.'-tariff'=> [
            'beginOffset' => 8,
            'endOffset' => 25,
        ]

    ];
    public const PRICE_DATA = [
        RoomTypeCategoryData::CATEGORY_ONE['fullTitle'] => [
            'price' => 1500,
            'singlePrice' => null,
            'additionalPrice' => null,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => null,
            'additionalPrices' => [],
            'additionalChildrenPrices' => [],
        ],
        RoomTypeCategoryData::NO_ADDITIONAL_PLACES_CATEGORY['fullTitle'] => [
            'price' => 1300,
            'singlePrice' => 1250,
            'additionalPrice' => null,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => null,
            'additionalPrices' => [],
            'additionalChildrenPrices' => [],
        ],
        RoomTypeCategoryData::ADDITIONAL_PLACES_CATEGORY['fullTitle'] => [
            'price' => 2450,
            'singlePrice' => 2400,
            'additionalPrice' => 900,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => 850,
            'additionalPrices' => [900, 800],
            'additionalChildrenPrices' => [850, 750],
        ],
    ];


    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +1 month');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $priceDecrementStep = 10;
        $accessor = $this->container->get('property_accessor');
        foreach ($hotels as $hotelNumber => $hotel) {
            $discount = 0;
            foreach (self::TARIFFS as $tariffKey => $offsets) {
                /** @var Tariff $tariff */
                $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                foreach (self::PRICE_DATA as $roomTypeCategoryKey => $priceData) {
                    /** @var RoomType $roomTypeCategory */
                    $roomTypeCategory = $this->getReference($roomTypeCategoryKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $beginOffsetDay = (clone $begin)->modify("+ {$offsets['beginOffset']}days");
                        $endOffsetDay = (clone $begin)->modify("+ {$offsets['endOffset']}days");

                        if ($beginOffsetDay > $day || $endOffsetDay < $day) {
                            continue;
                        }

                        $priceCache = new PriceCache();
                        $priceCache
                            ->setRoomTypeCategory($roomTypeCategory)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setDate($day);
                        if (!$roomTypeCategory->getIsHostel()) {
                            foreach (array_keys($priceData) as $priceValueKey) {
                                $value = $priceData[$priceValueKey];
                                if (\is_int($value) || \is_array($value)) {
                                    $value = $this->calcPrice($value, $discount);
                                }
                                $accessor->setValue($priceCache, $priceValueKey, $value);
                            }
                        }
                        $manager->persist($priceCache);
                    }

                }
                $discount += $priceDecrementStep;
            }
        }

        $manager->flush();
    }

    private function calcPrice($price, int $discount)
    {
        if (\is_int($price)) {
            $price -= $discount;

            return $price > 0 ? $price : 0;
        }

        if (\is_array($price)) {
            return array_map(function ($price) use ($discount) {
                $price = $this->calcPrice($price, $discount);
                return $price > 0 ? $price : null;
            }, $price);
        }
    }
    
    public function getOrder()
    {
        return 570;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }

}
