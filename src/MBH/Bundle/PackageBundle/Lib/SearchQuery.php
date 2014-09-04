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
     * Tariff id
     * 
     * @var string
     */
    public $tariff;
    
    public function addRoomType($id)
    {
        if (!in_array($id, $this->roomTypes) && !empty($id)) {
            $this->roomTypes[] = $id;
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
}
