<?php

namespace MBH\Bundle\PackageBundle\Document;


class CalculatedPackagePrice
{
    /**
     * mixed array of prices
     * @ODM\Field(type="hash")
     * @var array
     */
    protected $prices = [];

    /**
     * @ODM\EmbedMany(targetDocument="PackagePricesForCombination")
     * @var PackagePricesForCombination[]
     */
    protected $packagePrices = [];
}