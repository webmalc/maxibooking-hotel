<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Promotion;

class SearchQuery
{
    /**
     * @var \DateTime 
     */
    public $begin;
    
    /**
     * @var \DateTime 
     */
    public $end;

    /**
     * @var \DateTime
     */
    public $excludeBegin = null;

    /**
     * @var \DateTime
     */
    public $excludeEnd = null;
    
    /**
     * @var int
     */
    public $adults;
    
    /**
     * @var int
     */
    public $children;

    /**
     * @var array
     */
    public $childrenAges = [];

    /**
     * @var boolean
     */
    public $isOnline = false;
    
    /**
     * RoomTypes ids
     * 
     * @var []
     */
    public $roomTypes = [];

    /**
     * @var boolean
     */
    public $forceRoomTypes = false;

    /**
     * ExcludeRoomTypes ids
     *
     * @var []
     */
    public $excludeRoomTypes = [];

    /**
     * With accommodations on/off
     * @var bool
     */
    public $accommodations = false;

    /**
     * @var bool
     */
    public $grouped = false;

    /**
     * @var string
     */
    public $room;

    /**
     * @var Promotion
     */
    protected $promotion;
    
    /**
     * Tariff id
     * 
     * @var mixed
     */
    public $tariff;

    /**
     * @var RoomTypes ids
     */
    public $availableRoomTypes = [];

    /**
     * @var bool
     */
    public $forceBooking = false;

    public $infants = 0;
    
    public function addExcludeRoomType($id)
    {
        if (!in_array($id, $this->excludeRoomTypes) && !empty($id)) {
            $this->excludeRoomTypes[] = $id;
        }
    }

    public function addAvailableRoomType($id)
    {
        if (!in_array($id, $this->availableRoomTypes) && !empty($id)) {
            $this->availableRoomTypes[] = $id;
        }
    }

    /**
     * @param Hotel $hotel
     * @return $this
     */
    public function addHotel(Hotel $hotel = null)
    {
        if (empty($hotel)) {
            return $this;
        }
        $roomTypes = $hotel->getRoomTypes();
        foreach ($roomTypes as $roomType) {
            $this->addRoomType($roomType->getId());
        }

        return $this;
    }

    public function addRoomType($id)
    {
        if (!empty($this->availableRoomTypes) && !in_array($id, $this->availableRoomTypes)) {
            return false;
        }

        if (!in_array($id, $this->roomTypes) && !empty($id)) {
            $this->roomTypes[] = $id;
        }
    }

    /**
     * @param array $ages
     */
    public function setChildrenAges(array $ages)
    {
        foreach ($ages as $age) {
            if (is_numeric($age)) {
                $this->childrenAges[] = (int) $age;
            }
        }
    }

    /**
     * @return int
     */
    public function getTotalPlaces()
    {
        return (int) $this->adults + (int) $this->children;
    }

    /**
     * @return Promotion
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param mixed $promotion
     */
    public function setPromotion($promotion = false)
    {
        if (!$promotion instanceof Promotion && $promotion !== false) {
            $promotion = null;
        }

        $this->promotion = $promotion;
    }


}
