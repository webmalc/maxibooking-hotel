<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class RoomPrice
{
    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     */
    protected $roomType;
    
    /**
     * @var int
     * @ODM\Int(name="price")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $price;
    
    /**
     * @var int
     * @ODM\Int(name="additionalAdultPrice")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $additionalAdultPrice;
    
    /**
     * @var int
     * @ODM\Int(name="additionalChildPrice")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="Цена не может быть меньше нуля"
     * )
     */
    protected $additionalChildPrice;

    /**
     * Set roomType
     *
     * @param MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return self
     */
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * Get roomType
     *
     * @return MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return int $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set additionalAdultPrice
     *
     * @param int $additionalAdultPrice
     * @return self
     */
    public function setAdditionalAdultPrice($additionalAdultPrice)
    {
        $this->additionalAdultPrice = $additionalAdultPrice;
        return $this;
    }

    /**
     * Get additionalAdultPrice
     *
     * @return int $additionalAdultPrice
     */
    public function getAdditionalAdultPrice()
    {
        return $this->additionalAdultPrice;
    }

    /**
     * Set additionalChildPrice
     *
     * @param int $additionalChildPrice
     * @return self
     */
    public function setAdditionalChildPrice($additionalChildPrice)
    {
        $this->additionalChildPrice = $additionalChildPrice;
        return $this;
    }

    /**
     * Get additionalChildPrice
     *
     * @return int $additionalChildPrice
     */
    public function getAdditionalChildPrice()
    {
        return $this->additionalChildPrice;
    }
}
