<?php

namespace MBH\Bundle\PriceBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @ODM\Document(collection="PriceCache", repositoryClass="MBH\Bundle\PriceBundle\Document\PriceCacheRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"roomType", "date"}, message="PriceCache already exist.")
 */
class PriceCache extends Base
{
    /**
     * @var \MBH\Bundle\HotelBundle\Document\Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Assert\NotNull()
     */
    protected $hotel;

    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     */
    protected $roomType;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     */
    protected $tariff;

    /**
     * @var \DateTime
     * @ODM\Date()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $date;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(min=0)
     */
    protected $price;

    /**
     * @var boolean
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     */
    protected $isPersonPrice = false;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $additionalPrice = null;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $additionalChildrenPrice = null;

    /**
     * @var int
     * @ODM\Int()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    protected $singlePrice = null;


    /**
     * Set hotel
     *
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return self
     */
    public function setHotel(\MBH\Bundle\HotelBundle\Document\Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
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
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = (int) $price;
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
     * Set isPersonPrice
     *
     * @param boolean $isPersonPrice
     * @return self
     */
    public function setIsPersonPrice($isPersonPrice)
    {
        $this->isPersonPrice = (boolean) $isPersonPrice;
        return $this;
    }

    /**
     * Get isPersonPrice
     *
     * @return boolean $isPersonPrice
     */
    public function getIsPersonPrice()
    {
        return $this->isPersonPrice;
    }

    /**
     * Set additionalPrice
     *
     * @param int $additionalPrice
     * @return self
     */
    public function setAdditionalPrice($additionalPrice)
    {
        $this->additionalPrice = $additionalPrice;
        return $this;
    }

    /**
     * Get additionalPrice
     *
     * @return int $additionalPrice
     */
    public function getAdditionalPrice()
    {
        return $this->additionalPrice;
    }

    /**
     * Set additionalChildrenPrice
     *
     * @param int $additionalChildrenPrice
     * @return self
     */
    public function setAdditionalChildrenPrice($additionalChildrenPrice)
    {
        $this->additionalChildrenPrice = $additionalChildrenPrice;
        return $this;
    }

    /**
     * Get additionalChildrenPrice
     *
     * @return int $additionalChildrenPrice
     */
    public function getAdditionalChildrenPrice()
    {
        return $this->additionalChildrenPrice;
    }

    /**
     * Set singlePrice
     *
     * @param int $singlePrice
     * @return self
     */
    public function setSinglePrice($singlePrice)
    {
        $this->singlePrice = $singlePrice;
        return $this;
    }

    /**
     * Get singlePrice
     *
     * @return int $singlePrice
     */
    public function getSinglePrice()
    {
        return $this->singlePrice;
    }
}
