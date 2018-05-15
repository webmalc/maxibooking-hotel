<?php


namespace MBH\Bundle\SearchBundle\Services\Calc\Calculator;


use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\SearchBundle\Services\Calc\PriceHelper;

interface PriceCalculatorInterface
{
    public function calculate(PriceCache $priceCache, int $mainAdults, int $mainChildren, int $addAdults, int $addChildren): PriceHelper;
}