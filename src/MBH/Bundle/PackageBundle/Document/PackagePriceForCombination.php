<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class PackagePriceForCombination
 * @package MBH\Bundle\PackageBundle\Document
 */
class PackagePriceForCombination
{
    /**
     * @var float
     * @ODM\Field(type="float")
     */
    private $total;

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
     * @return float
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return PackagePriceForCombination
     */
    public function setTotal(float $total): PackagePriceForCombination
    {
        $this->total = $total;

        return $this;
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
     * @return PackagePriceForCombination
     */
    public function setPackagePrices($packagePrices): PackagePriceForCombination
    {
        $this->packagePrices = $packagePrices;

        return $this;
    }

    /**
     * @param PackagePrice $packagePrice
     * @return PackagePriceForCombination
     */
    public function addPackagePrice(PackagePrice $packagePrice)
    {
        $this->packagePrices->add($packagePrice);

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return PackagePrice|null
     */
    public function getPackagePriceOnDate(\DateTime $date)
    {
        foreach ($this->getPackagePrices() as $packagePrice) {
            if ($packagePrice->getDate() === $date) {
                return $packagePrice;
            }
        }

        return null;
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
     * @return PackagePriceForCombination
     */
    public function setAdults(int $adults): PackagePriceForCombination
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
     * @return PackagePriceForCombination
     */
    public function setChildren(int $children): PackagePriceForCombination
    {
        $this->children = $children;

        return $this;
    }
}