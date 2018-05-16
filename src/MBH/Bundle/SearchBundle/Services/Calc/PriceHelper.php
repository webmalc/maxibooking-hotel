<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\PriceBundle\Document\PriceCache;

class PriceHelper
{
    /** @var PriceCache */
    private $priceChache;

    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var CalcQuery */
    private $calcHelper;

}