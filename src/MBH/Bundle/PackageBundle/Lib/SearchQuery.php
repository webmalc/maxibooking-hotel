<?php

namespace MBH\Bundle\PackageBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Component\Validator\Constraints as Assert;

class SearchQuery
{
    /**
     * @var bool
     */
    public $memcached = true;

    /**
     * @var \DateTime
     * @Assert\NotNull(message="form.searchType.check_in_date_not_filled")
     * @Assert\Date()
     */
    public $begin;
    
    /**
     * @var \DateTime
     * @Assert\NotNull(message="orm.searchType.check_out_date_not_filled")
     * @Assert\Date()
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
     * @Assert\NotNull(message="form.searchType.adults_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 10,
     *     minMessage = "form.searchType.adults_amount_less_zero"
     * )
     */
    public $adults;
    
    /**
     * @var int
     * @Assert\NotNull(message="orm.searchType.children_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
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
     * @var Special
     */
    protected $special;
    
    /**
     * Tariff id
     * 
     * @var mixed
     */
    public $tariff;

    /**
     * Additional days for search
     * @var int
     * @Assert\Range(
     *     min = 0,
     *     max = 10,
     *     maxMessage = "form.searchType.range_validator",
     *     minMessage = "form.searchType.range_validator"
     * )
     */
    public $range = 0;

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

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return SearchQuery
     */
    public function setSpecial(Special $special = null): SearchQuery
    {
        $this->special = $special;
        return $this;
    }
}
