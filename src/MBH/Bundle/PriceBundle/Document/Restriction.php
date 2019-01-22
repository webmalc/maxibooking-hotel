<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Restriction", repositoryClass="MBH\Bundle\PriceBundle\Document\RestrictionRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"roomType", "date", "tariff"}, message="Restriction already exist.")
 * @ODM\Index(name="search_roomtype_tariff_date", keys={"roomType"="asc","tariff"="asc","date"="asc"})
 * @ODM\Index(name="search_roomtype_date", keys={"roomType"="asc","date"="asc"})
 */
class Restriction extends Base
{
    /**
     * @var \MBH\Bundle\HotelBundle\Document\Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $tariff;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @ODM\Index()
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $date;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $minStay = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $maxStay = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $minStayArrival = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $maxStayArrival = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $minBeforeArrival = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $maxBeforeArrival = null;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $maxGuest;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=1)
     */
    protected $minGuest;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $closedOnArrival = false;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $closedOnDeparture = false;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    protected $closed = false;

    /**
     * Set hotel
     *
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
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
     * @param RoomType $roomType
     * @return self
     */
    public function setRoomType(RoomType $roomType)
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
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(Tariff $tariff)
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
     * Set date
     *
     * @param \DateTime $date
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
     * @return \DateTime $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set minStay
     *
     * @param int $minStay
     * @return self
     */
    public function setMinStay($minStay)
    {
        $this->minStay = $minStay;
        return $this;
    }

    /**
     * Get minStay
     *
     * @return int $minStay
     */
    public function getMinStay()
    {
        return $this->minStay;
    }

    /**
     * Set maxStay
     *
     * @param int $maxStay
     * @return self
     */
    public function setMaxStay($maxStay)
    {
        $this->maxStay = $maxStay;
        return $this;
    }

    /**
     * Get maxStay
     *
     * @return int $maxStay
     */
    public function getMaxStay()
    {
        return $this->maxStay;
    }

    /**
     * Set minStayArrival
     *
     * @param int $minStayArrival
     * @return self
     */
    public function setMinStayArrival($minStayArrival)
    {
        $this->minStayArrival = $minStayArrival;
        return $this;
    }

    /**
     * Get minStayArrival
     *
     * @return int $minStayArrival
     */
    public function getMinStayArrival()
    {
        return $this->minStayArrival;
    }

    /**
     * Set maxStayArrival
     *
     * @param int $maxStayArrival
     * @return self
     */
    public function setMaxStayArrival($maxStayArrival)
    {
        $this->maxStayArrival = $maxStayArrival;
        return $this;
    }

    /**
     * Get maxStayArrival
     *
     * @return int $maxStayArrival
     */
    public function getMaxStayArrival()
    {
        return $this->maxStayArrival;
    }

    /**
     * Set minBeforeArrival
     *
     * @param int $minBeforeArrival
     * @return self
     */
    public function setMinBeforeArrival($minBeforeArrival)
    {
        $this->minBeforeArrival = $minBeforeArrival;
        return $this;
    }

    /**
     * Get minBeforeArrival
     *
     * @return int $minBeforeArrival
     */
    public function getMinBeforeArrival()
    {
        return $this->minBeforeArrival;
    }

    /**
     * Set maxBeforeArrival
     *
     * @param int $maxBeforeArrival
     * @return self
     */
    public function setMaxBeforeArrival($maxBeforeArrival)
    {
        $this->maxBeforeArrival = $maxBeforeArrival;
        return $this;
    }

    /**
     * Get maxBeforeArrival
     *
     * @return int $maxBeforeArrival
     */
    public function getMaxBeforeArrival()
    {
        return $this->maxBeforeArrival;
    }
    /**
     * @return int
     */
    public function getMaxGuest()
    {
        return $this->maxGuest;
    }
    /**
     * @param int $maxGuest
     * @return Restriction
     */
    public function setMaxGuest($maxGuest)
    {
        $this->maxGuest = $maxGuest;
        return $this;
    }
    /**
     * @return int
     */
    public function getMinGuest()
    {
        return $this->minGuest;
    }
    /**
     * @param int $minGuest
     * @return Restriction
     */
    public function setMinGuest($minGuest)
    {
        $this->minGuest = $minGuest;
        return $this;
    }
    /**
     * Set closedOnArrival
     *
     * @param boolean $closedOnArrival
     * @return self
     */
    public function setClosedOnArrival($closedOnArrival)
    {
        $this->closedOnArrival = $closedOnArrival;
        return $this;
    }

    /**
     * Get closedOnArrival
     *
     * @return boolean $closedOnArrival
     */
    public function getClosedOnArrival()
    {
        return $this->closedOnArrival;
    }

    /**
     * Set closedOnDeparture
     *
     * @param boolean $closedOnDeparture
     * @return self
     */
    public function setClosedOnDeparture($closedOnDeparture)
    {
        $this->closedOnDeparture = $closedOnDeparture;
        return $this;
    }

    /**
     * Get closedOnDeparture
     *
     * @return boolean $closedOnDeparture
     */
    public function getClosedOnDeparture()
    {
        return $this->closedOnDeparture;
    }

    /**
     * Set closedOnDeparture
     *
     * @param boolean $closed
     * @return self
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
        return $this;
    }

    /**
     * Get closedOnDeparture
     *
     * @return boolean $closedOnDeparture
     */
    public function getClosed()
    {
        return $this->closed;
    }
}
