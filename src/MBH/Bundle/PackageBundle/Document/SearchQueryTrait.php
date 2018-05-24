<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PriceBundle\Document\Tariff;

trait SearchQueryTrait
{
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    public $excludeBegin = null;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    public $excludeEnd = null;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     */
    public $isOnline = false;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     */
    public $forceRoomTypes = false;

    /**
     * ExcludeRoomTypes ids
     * @ODM\Field(type="collection")
     * @var []
     */
    public $excludeRoomTypes = [];

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $grouped = false;

    /**
     * @var Promotion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    protected $promotion;

    /**
     * @var Special
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Special")
     */
    protected $special;

    /**
     * @var RoomType[]|array ids
     * @ODM\Field(type="collection")
     */
    public $availableRoomTypes = [];

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $forceBooking = false;


    /**
     * @param $id
     */
    public function addExcludeRoomType($id)
    {
        if (!in_array($id, $this->excludeRoomTypes) && !empty($id)) {
            $this->excludeRoomTypes[] = $id;
        }
    }

    /**
     * @param $id
     */
    public function addAvailableRoomType($id)
    {
        if (!in_array($id, $this->availableRoomTypes) && !empty($id)) {
            $this->availableRoomTypes[] = $id;
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
     * @return static
     */
    public function setSpecial(Special $special = null)
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @param Tariff $tariff
     * @return static
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }
}