<?php

namespace MBH\Bundle\PackageBundle\Lib;

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
     * @var \DateTime 
     */
    public $children;
    
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
        if (!in_array($id, $this->roomTypes)) {
            $this->roomTypes[] = $id;
        }
    }
}
