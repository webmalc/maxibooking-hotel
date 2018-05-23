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
class AdditionalPriceCacheCategoryData extends AbstractFixture implements OrderedFixtureInterface
{

    public const TARIFFS = [
        'main-tariff' => [
            'beginOffset' => 0,
            'endOffset' => 0,
        ],
        'downTariff-tariff'=> [
            'beginOffset' => 4,
            'endOffset' => 15,
        ],
        'upTariff-tariff'=> [
            'beginOffset' => 8,
            'endOffset' => 5,
        ]

    ];
    public const PRICE_DATA = [
        'categoryOne' => [
            'price' => 1500,
            'single' => null,
            'additional' => null,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildPrice' => null,
            'individualAdultPrices' => [],
            'individualChildrenPrices' => [],
        ],
        'categoryTwo' => [
            'price' => 1300,
            'single' => 1250,
            'additional' => 999,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildPrice' => null,
            'individualAdultPrices' => [],
            'individualChildrenPrices' => [],
        ],
        'categoryThree' => [
            'price' => 2450,
            'single' => 2400,
            'additional' => 900,
            'isPersonPrice' => false,
            'childPrice' => null,
            'additionalChildPrice' => 800,
            'individualAdultPrices' => [900, 350, 340],
            'individualChildrenPrices' => [850, 240, 230],
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
        foreach ($hotels as $hotelNumber => $hotel) {
            $discount = 0;
            foreach (self::TARIFFS as $tariffKey => $offsets) {
                /** @var Tariff $tariff */
                $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                foreach (self::PRICE_DATA as $roomTypeCategoryKey => $priceData) {
                    /** @var RoomType $roomTypeCategory */
                    $roomTypeCategory = $this->getReference($roomTypeCategoryKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $actualBeginOffset = (int)$day->diff($begin)->format('%d');
                        $actualEndOffset = (int)$day->diff($end)->format('%d');
                        if ($actualBeginOffset < $offsets['beginOffset'] || $actualEndOffset < $offsets['endOffset']) {
                            continue;
                        }

                        $priceCache = new PriceCache();
                        $priceCache
                            ->setRoomTypeCategory($roomTypeCategory)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setDate($day);
                        if (!$roomTypeCategory->getIsHostel()) {
                            $priceData['price'] === null ?: $priceCache->setPrice($this->calcPrice($priceData['price'], $discount));
                            $priceData['single'] === null ?: $priceCache->setSinglePrice($this->calcPrice($priceData['single'], $discount));
                            $priceData['additional'] === null ?: $priceCache->setAdditionalPrice($this->calcPrice($priceData['additional'], $discount));
                            $priceData['isPersonPrice'] === null ?: $priceCache->setIsPersonPrice($priceData['isPersonPrice']);
                            $priceData['childPrice'] === null ?: $priceCache->setChildPrice($this->calcPrice($priceData['childPrice'], $discount));
                            $priceData['additionalChildPrice'] === null ?: $priceCache->setAdditionalChildrenPrice($this->calcPrice($priceData['additionalChildPrice'], $discount));
                            $priceData['individualAdultPrices'] === null ?: $priceCache->setAdditionalPrices(array_map(function ($price) use ($discount) {
                                $price = $this->calcPrice($price, $discount);
                                return $price > 0 ? $price: null;
                            }, $priceData['individualAdultPrices']));
                            $priceData['individualChildrenPrices'] === null ?: $priceCache->setAdditionalChildrenPrices(array_map(function ($price) use ($discount) {
                                $price = $this->calcPrice($price, $discount);
                                return $price > 0 ? $price: null;
                            }, $priceData['individualChildrenPrices']));
                        } else {
                            $priceCache->setPrice($priceData['price'] - $discount);
                        }
                        $manager->persist($priceCache);
                    }

                }
                $discount += $priceDecrementStep;
            }
        }

        $manager->flush();
    }

    private function calcPrice(int $price, int $discount): int
    {
        $price -= $discount;

        return $price > 0 ? $price : 0;
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
