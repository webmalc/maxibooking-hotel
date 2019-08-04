<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\Price;

interface PriceBuilderInterface
{
    public function createInstance(): PriceBuilderInterface;

    public function setAdults(int $adults): PriceBuilderInterface;

    public function setChildren(int $children): PriceBuilderInterface;

    public function setChildrenAges(array $childrenAges): PriceBuilderInterface;

    public function addDayPrice(DayPrice $dayPrice): PriceBuilderInterface;

    public function setTotal($total): PriceBuilderInterface;

    public function getPrice(): Price;
}