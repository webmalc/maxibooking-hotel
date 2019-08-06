<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;

class ChildFreeTariffDayPriceDirector
{
    /** @var DayPriceBuilder */
    private $dayPriceBuilder;

    /**
     * ChildFreeTariffDayPriceDirector constructor.
     * @param DayPriceBuilder $dayPriceBuilder
     */
    public function __construct(DayPriceBuilder $dayPriceBuilder)
    {
        $this->dayPriceBuilder = $dayPriceBuilder;
    }

    public function packagePriceConvert(PackagePrice $packagePrice): DayPrice
    {
        $this->dayPriceBuilder
            ->createInstance()
            ->setTotal($dayPrice)
        ;

        return $this->dayPriceBuilder->getDayPrice();
    }


}