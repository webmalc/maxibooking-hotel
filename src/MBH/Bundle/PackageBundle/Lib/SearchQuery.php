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
     * @var int
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
        if (!in_array($id, $this->roomTypes) && !empty($id)) {
            $this->roomTypes[] = $id;
        }
    }
}
