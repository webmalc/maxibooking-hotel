<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument()
 * Class SpecialPrice
 * @package MBH\Bundle\PriceBundle\Services
 */
class SpecialPrice
{
    /**
     * @var float
     * @ODM\Field(type="float")
     */
    protected $price;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $childrenCount;

    /**
     * @var int
     * @ODM\Field(type="int")
     */
    protected $adultsCount;

    /**
     * @var Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    protected $tariff;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomType;

    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return SpecialPrice
     */
    public function setTariff(Tariff $tariff): SpecialPrice
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return SpecialPrice
     */
    public function setPrice($price): SpecialPrice
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return integer
     */
    public function getChildrenCount()
    {
        return $this->childrenCount;
    }

    /**
     * @param mixed $childrenCount
     * @return SpecialPrice
     */
    public function setChildrenCount($childrenCount): SpecialPrice
    {
        $this->childrenCount = $childrenCount;

        return $this;
    }

    /**
     * @return integer
     */
    public function getAdultsCount()
    {
        return $this->adultsCount;
    }

    /**
     * @param mixed $adultsCount
     * @return SpecialPrice
     */
    public function setAdultsCount($adultsCount): SpecialPrice
    {
        $this->adultsCount = $adultsCount;

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
     * @return SpecialPrice
     */
    public function setRoomType(RoomType $roomType): SpecialPrice
    {
        $this->roomType = $roomType;

        return $this;
    }
}