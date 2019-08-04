<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;

class PriceBuilder implements PriceBuilderInterface
{

    /** @var Price */
    private $price;

    public function createInstance(): PriceBuilderInterface
    {
        $this->price = new Price();

        return $this;
    }

    public function setAdults(int $adults): PriceBuilderInterface
    {
        $this->price->setAdults($adults);

        return $this;
    }

    public function setChildren(int $children): PriceBuilderInterface
    {
        $this->price->setChildren($children);

        return $this;
    }

    public function setChildrenAges(array $childrenAges): PriceBuilderInterface
    {
        $this->price->setChildrenAges($childrenAges);

        return $this;
    }

    public function addDayPrice(DayPrice $dayPrice): PriceBuilderInterface
    {
        $this->price->addPriceByDay($dayPrice);

        return $this;
    }

    public function setTotal($total): PriceBuilderInterface
    {
        $this->price->setTotal($total);

        return $this;
    }

    public function getPrice(): Price
    {
        $price = $this->price;
        unset($this->price);

        return $price;
    }

}