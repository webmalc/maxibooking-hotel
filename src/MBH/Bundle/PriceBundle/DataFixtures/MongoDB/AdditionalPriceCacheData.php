<?php

namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\PriceCache;

/**
 * Class PriceCacheData
 */
class AdditionalPriceCacheData extends AbstractFixture implements OrderedFixtureInterface
{
    /**TODO: begin offset - offset from begin, end offset offset from end (one month default)*/
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
        AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'] => [
            'price' => 1000,
            'singlePrice' => null,
            'additionalPrice' => null,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => null,
            'additionalPrices' => [],
            'additionalChildrenPrices' => [],
        ],
        AdditionalRoomTypeData::THREE_PLACE_ROOM_TYPE['fullTitle'] => [
            'price' => 1000,
            'singlePrice' => 900,
            'additionalPrice' => null,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => null,
            'additionalPrices' => [],
            'additionalChildrenPrices' => [],
        ],
        AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'] => [
            'price' => 2000,
            'singlePrice' => 1900,
            'additionalPrice' => 1500,
            'isPersonPrice' => true,
            'childPrice' => 1300,
            'additionalChildrenPrice' => 700,
            'additionalPrices' => [1500, 1450],
            'additionalChildrenPrices' => [700, 1250],
        ],
        AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle']=> [
            'price' => 2000,
            'singlePrice' => 1900,
            'additionalPrice' => 1500,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildrenPrice' => 700,
            'additionalPrices' => [1500, 300],
            'additionalChildrenPrices' => [700, 150],
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
                foreach (self::PRICE_DATA as $roomTypeKey => $priceData) {
                    /** @var RoomType $roomType */
                    $roomType = $this->getReference($roomTypeKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $beginOffsetDay = (clone $begin)->modify("+ {$offsets['beginOffset']}days");
                        $endOffsetDay = (clone $begin)->modify("+ {$offsets['endOffset']}days");

                        if ($beginOffsetDay > $day || $endOffsetDay < $day) {
                            continue;
                        }

                        /** disabled price cached must be for tests */
                        foreach ([true, false] as $isEnabled) {
                            $priceCache = new PriceCache();
                            $priceCache
                                ->setRoomType($roomType)
                                ->setHotel($hotel)
                                ->setTariff($tariff)
                                ->setDate($day)
                                ->setIsEnabled($isEnabled)

                            ;
                            if (!$roomType->getIsHostel()) {
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
