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
     * Tariff id
     * 
     * @var mixed
     */
    public $tariff;
    
    public function addRoomType($id)
    {
        if (!in_array($id, $this->roomTypes) && !empty($id)) {
            $this->roomTypes[] = $id;
        }
    }

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

    /**
     * @return int
     */
    public function getTotalPlaces()
    {
        return (int) $this->adults + (int) $this->children;
    }
}
