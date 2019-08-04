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
     * @var int
     */
    private $priceRoundSign;

    /**
     * PriceDirector constructor.
     * @param PriceBuilderInterface $priceBuilder
     * @param int $priceRoundSign
     */
    public function __construct(PriceBuilderInterface $priceBuilder, int $priceRoundSign = 2)
    {
        $this->priceBuilder = $priceBuilder;
        $this->priceRoundSign = $priceRoundSign;
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

        $total = 0;
        foreach ($dayPrices as $dayPrice) {
            /** @var DayPrice $dayPrice */
            $this->priceBuilder->addDayPrice($dayPrice);
            $total += $dayPrice->getTotal();
        }

        if (null !== $this->priceRoundSign) {
            $total = round($total, $this->priceRoundSign);
        }

        $this->priceBuilder->setTotal($total);

        return $this->priceBuilder->getPrice();
    }

}