<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * @ODM\Document()
 * Class CalculatedPackagePrice
 * @package MBH\Bundle\PackageBundle\Document
 */
class CalculatedPackagePrices
{
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    private $begin;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    private $end;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="RoomType")
     */
    private $roomType;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="Tariff")
     */
    private $tariff;

    /**
     * @ODM\EmbedMany(targetDocument="PackagePriceForCombination")
     * @var PackagePriceForCombination[]
     */
    private $packagePrices;

    /**
     * @var Promotion
     * @ODM\ReferenceOne(targetDocument="Promotion")
     */
    private $promotion;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    private $useCategories;

    /**
     * @var Special
     * @ODM\ReferenceOne(targetDocument="Special")
     */
    private $special;

    public function __construct()
    {
        $this->packagePrices = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return CalculatedPackagePrices
     */
    public function setBegin(\DateTime $begin): CalculatedPackagePrices
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return CalculatedPackagePrices
     */
    public function setEnd(\DateTime $end): CalculatedPackagePrices
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return CalculatedPackagePrices
     */
    public function setRoomType(RoomType $roomType): CalculatedPackagePrices
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return CalculatedPackagePrices
     */
    public function setTariff(Tariff $tariff): CalculatedPackagePrices
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return Promotion
     */
    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    /**
     * @param Promotion $promotion
     * @return CalculatedPackagePrices
     */
    public function setPromotion(Promotion $promotion): CalculatedPackagePrices
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @param PackagePriceForCombination $priceForCombination
     * @return CalculatedPackagePrices
     */
    public function addPackagePrice(PackagePriceForCombination $priceForCombination)
    {
        $this->packagePrices->add($priceForCombination);

        return $this;
    }

    /**
     * @return ArrayCollection|PackagePriceForCombination[]
     */
    public function getPackagePrices()
    {
        return $this->packagePrices;
    }

    /**
     * @return bool
     */
    public function isUseCategories(): ?bool
    {
        return $this->useCategories;
    }

    /**
     * @param bool $useCategories
     * @return CalculatedPackagePrices
     */
    public function setUseCategories(bool $useCategories): CalculatedPackagePrices
    {
        $this->useCategories = $useCategories;

        return $this;
    }

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return CalculatedPackagePrices
     */
    public function setSpecial(Special $special): CalculatedPackagePrices
    {
        $this->special = $special;

        return $this;
    }
}