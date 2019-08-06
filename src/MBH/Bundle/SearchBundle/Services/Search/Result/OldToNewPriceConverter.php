<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result;

use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\DayPriceBuilder;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\PriceBuilder;

class OldToNewPriceConverter
{
    public function oldToNewPriceConvert(array $oldPrices, string $roomType)
    {
        $prices = [];
        foreach ($oldPrices as $oldPrice) {
            $packagePrices = $oldPrice['packagePrices'];
            $adults = $oldPrice['adults'];
            $children = $oldPrice['children'];
            $dayPrices = $this->createDayPrices(
                $adults,
                $children,
                $roomType,
                $packagePrices
            );
            $priceBuilder = new PriceBuilder();

            $priceBuilder
                ->createInstance()
                ->setAdults($adults)
                ->setChildren($children)
                ->setChildrenAges($oldPrice['childrenAges']);

            $total = 0;
            foreach ($dayPrices as $dayPrice) {
                /** @var DayPrice $dayPrice */
                $priceBuilder->addDayPrice($dayPrice);
                $total += $dayPrice->getTotal();
            }


            $total = round($total);
            $priceBuilder->setTotal($total);

            $prices[] =  $priceBuilder->getPrice();
        }

        return $prices;
    }

    private function createDayPrices(int $adults, int $children, string $roomType, array $packagePrices): array
    {
        $dayPriceBuilder = new DayPriceBuilder();
        $dayPrices = [];
        foreach ($packagePrices as $packagePrice) {
            /** @var PackagePrice $packagePrice */
            $dayPrice = $dayPriceBuilder
                ->createInstance()
                ->setAdults($adults)
                ->setChildren($children)
                ->setTariff($packagePrice->getTariff()->getId())
                ->setRoomType($roomType)
                ->setTotal($packagePrice->getPrice())
                ->setDate($packagePrice->getDate())
                ->setAdditionalAdults(0)
                ->setAdditionalChildren(0)
            ;
            if ($promotion = $packagePrice->getPromotion()) {
                $dayPrice->setPromotion($promotion->getId());
            }
            $dayPrices[] = $dayPrice->getDayPrice();
        }

        return $dayPrices;
    }

}