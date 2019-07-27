<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use MBH\Bundle\SearchBundle\Services\Calc\CalcQueryInterface;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;

class PriceDirector
{

    /** @var PriceBuilderInterface */
    private $priceBuilder;

    /**
     * PriceDirector constructor.
     * @param PriceBuilderInterface $priceBuilder
     */
    public function __construct(PriceBuilderInterface $priceBuilder)
    {
        $this->priceBuilder = $priceBuilder;
    }


    /**
     * @param CalcQueryInterface $calcQuery
     * @param array $dayPrices
     * @return Price
     */
    public function createPrice(CalcQueryInterface $calcQuery, array $dayPrices): Price
    {
        $this->priceBuilder
            ->createInstance()
            ->setAdults($calcQuery->getAdults())
            ->setChildren($calcQuery->getChildren())
            ->setChildrenAges($calcQuery->getChildrenAges());

        foreach ($dayPrices as $dayPrice) {
            /** @var DayPrice $dayPrice */
            $this->priceBuilder->addDayPrice($dayPrice);
        }

        return $this->priceBuilder->getPrice();
    }

}