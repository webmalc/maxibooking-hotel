<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document(collection="RoomCache", repositoryClass="MBH\Bundle\PackageBundle\Document\RoomCacheRepository")
 * @Gedmo\Loggable
 */
class RoomCache extends Base
{   
    /** 
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    protected $tariff;
    
    /** 
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomType;
    
    /**
     * @var \DateTime
     * @ODM\Date(name="date")
     */
    protected $date;
    
    /**
     * @var int
     * @ODM\Int()
     */
    protected $totalRooms;
    
    /**
     * @var int
     * @ODM\Int()
     */
    protected $rooms;
    
    /**
     * @var int
     * @ODM\Boolean()
     */
    protected $isDefault;

    /**
     * @var int
     * @ODM\Boolean()
     */
    protected $isOnline;
    
    /**
     * @var int
     * @ODM\Int()
     */
    protected $places;


    /** 
     * @var PriceCache[]
     * @ODM\EmbedMany(targetDocument="PriceCache")
     */
    protected $prices;
    

    public function __construct()
    {
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return \DateTime $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set totalRooms
     *
     * @param int $totalRooms
     * @return self
     */
    public function setTotalRooms($totalRooms)
    {
        $this->totalRooms = $totalRooms;
        return $this;
    }

    /**
     * Get totalRooms
     *
     * @return int $totalRooms
     */
    public function getTotalRooms()
    {
        return $this->totalRooms;
    }

    /**
     * Set rooms
     *
     * @param int $rooms
     * @return self
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
        return $this;
    }

    /**
     * Get rooms
     *
     * @return int $rooms
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Set places
     *
     * @param int $places
     * @return self
     */
    public function setPlaces($places)
    {
        $this->places = $places;
        return $this;
    }

    /**
     * Get places
     *
     * @return int $places
     */
    public function getPlaces()
    {
        return $this->places;
    }
    
    /**
     * Add price
     *
     * @param \MBH\Bundle\PackageBundle\Document\PriceCache $price
     */
    public function addPrice(\MBH\Bundle\PackageBundle\Document\PriceCache $price)
    {
        $this->prices[] = $price;
    }

    /**
     * Remove price
     *
     * @param \MBH\Bundle\PackageBundle\Document\PriceCache $price
     */
    public function removePrice(\MBH\Bundle\PackageBundle\Document\PriceCache $price)
    {
        $this->prices->removeElement($price);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection $prices
     */
    public function getPrices()
    {
        return $this->prices;
    }


    /**
     * @param array $prices
     * @return $this|array
     */
    public function setPrices($prices)
    {
        return $this->prices = $prices;

        return $this;
    }
    
    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     * @return self
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean $isOnline
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }
    
    
    public function getPrice($adults = 1, $children = 0)
    {
        foreach ($this->getPrices() as $price)
        {
            if ($price->getAdults() == $adults && $price->getChildren() == $children){
                return $price;
            }
        }
        
        return null;
    }        
}
