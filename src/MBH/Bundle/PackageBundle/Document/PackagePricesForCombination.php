<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class PackagePricesForCombination
{
    /**
     * @var PackagePrice[]
     * @ODM\EmbedMany(targetDocument="PackagePrice")
     */
    private $packagePrices;

    /**
     * @ODM\Field(type="int")
     * @var integer
     */
    private $adults;

    /**
     * @ODM\Field(type="int")
     * @var integer
     */
    private $children;

    public function __construct() {
        $this->packagePrices = new ArrayCollection();
    }

    /**
     * @return PackagePrice[]
     */
    public function getPackagePrices()
    {
        return $this->packagePrices;
    }

    /**
     * @param PackagePrice[] $packagePrices
     * @return PackagePricesForCombination
     */
    public function setPackagePrices($packagePrices): PackagePricesForCombination
    {
        $this->packagePrices = $packagePrices;

        return $this;
    }

    /**
     * @param PackagePrice $packagePrice
     * @return PackagePricesForCombination
     */
    public function addPackagePrice(PackagePrice $packagePrice)
    {
        $this->packagePrices->add($packagePrice);

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return PackagePricesForCombination
     */
    public function setAdults(int $adults): PackagePricesForCombination
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): ?int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return PackagePricesForCombination
     */
    public function setChildren(int $children): PackagePricesForCombination
    {
        $this->children = $children;

        return $this;
    }
}