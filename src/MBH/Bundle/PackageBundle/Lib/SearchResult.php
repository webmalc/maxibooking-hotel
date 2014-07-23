<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\RoomType;

class SearchResult
{

    /**
     * @var \DateTime 
     */
    protected $begin;

    /**
     * @var \DateTime 
     */
    protected $end;

    /**
     * @var int
     */
    protected $adults;

    /**
     * @var int
     */
    protected $children;

    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var Tariff 
     */
    protected $tariff;

    /**
     * @var $foods
     */
    protected $foods = [];

    /**
     * mixed array of prices
     * 
     * @var []
     */
    protected $prices = [];

    /**
     * @var int 
     */
    protected $roomsCount = 0;

    /**
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * 
     * @return EoomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @return []
     */
    public function getFoods()
    {
        return $this->foods;
    }

    /**
     * @return []
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param \DateTime $begin
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @param \DateTime $end
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @param \MBH\Bundle\HotelBundle\Form\RoomType $roomType
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setRoomType(RoomType $roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @param array $foods
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setFoods(array $foods)
    {
        $this->foods = $foods;

        return $this;
    }

    /**
     * @param array $prices
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->getNights() + 1;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->end->diff($this->begin)->format("%a");
    }

    
    /**
     * @param string $food
     * @param mixed $price
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function addFood($food, $price)
    {
        if ($price === null) {
            return $this;
        }
        if (!in_array($food, $this->foods)) {
            $this->foods[] = $food;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getRoomsCount()
    {
        return (int) $this->roomsCount;
    }

    /**
     * @param int $roomsCount
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setRoomsCount($roomsCount)
    {
        $this->roomsCount = (int) $roomsCount;

        return $this;
    }

    /**
     * @param string $food
     * @param mixed $price
     * @param mixed $adults
     * @param mixed $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function addPrice($food, $price, $adults = null, $children = null)
    {
        if ($adults !== null &&  $children !== null) {
            if(($adults != $this->getAdults()) || ($children != $this->getChildren())) {
                return $this;
            }
        }
        if($price === null) {
            return $this;
        }
        
        if (!isset($this->prices[$food])) {
            $this->prices[$food] = 0;
        }

        $this->prices[$food] += (int) $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param type $adults
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;
        
        return $this;
    }

    /**
     * @param type $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setChildren($children)
    {
        $this->children = $children;
        
        return $this;
    }

}
