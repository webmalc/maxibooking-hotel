<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;

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
     * Tariff id
     * 
     * @var mixed
     */
    public $tariff;

    /**
     * @var float
     */
    public $distance;
    /**
     * @var string
     */
    public $highway;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $sort;

    /**
     * @var int
     */
    public $skip;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var string Hotel ID
     */
    public $hotel;

    /**
     * @var String
     */
    public $district;

    public function addExcludeRoomType($id)
    {
        if (!in_array($id, $this->excludeRoomTypes) && !empty($id)) {
            $this->excludeRoomTypes[] = $id;
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

        if(count($roomTypes)) {
            foreach ($hotel->getRoomTypes() as $roomType) {
                $this->addRoomType($roomType->getId());
            }
        } else {
            $this->addRoomType($hotel->getId() . ': empty roomTypes');
        }
        return $this;
    }

    public function addRoomType($id)
    {
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
}
