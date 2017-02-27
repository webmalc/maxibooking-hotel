<?php namespace MBH\Bundle\WarehouseBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;

class RecordQuery
{
    /**
     * @var boolean
     */
    private $isSystem;

    /**
     * @var string
     */
    private $fullTitle;

    /**
     * @var \DateTime
     */
    private $recordDate;

    /**
     * @var \DateTime
     */
    private $recordDateFrom;

    /**
     * @var \DateTime
     */
    private $recordDateTo;

    /**
     * @var string
     */
    private $operation;

    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @var WareItem
     */
    private $wareItem;

    /**
     * @var WareCategory
     */
    private $wareCategory;

    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $qtty;

    /**
     * @var float
     */
    private $amount;

    private $sortBy;

    private $sortDirection;

    /**
     * @var boolean
     */
    public $isEnabled;

    /**
     * @var string
     */
    public $search;

    /**
     * Get sort direction
     *
     * @return mixed
     */
    public function getSortDirection() {
        return $this->sortDirection;
    }

    /**
     * Set sort direction
     *
     * @param null $param
     * @return $this
     */
    public function setSortDirection($param = null) {
        $this->sortDirection = $param;

        return $this;
    }

    /**
     * Set sort by
     *
     * @param $sortBy
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * Get sort by
     *
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set ware item
     *
     * @param WareItem $item
     * @return $this
     */
    public function setWareItem($item)
    {
        $this->wareItem = $item;

        return $this;
    }

    /**
     * Get ware item
     *
     * @return WareItem
     */
    public function getWareItem()
    {
        return $this->wareItem;
    }

    /**
     * Set ware category
     *
     * @param WareCategory $category
     * @return $this
     */
    public function setWareCategory($category)
    {
        $this->wareCategory = $category;

        return $this;
    }

    /**
     * Get ware category
     *
     * @return WareCategory
     */
    public function getWareCategory()
    {
        return $this->wareCategory;
    }

    /**
     * Set hotel
     *
     * @param Hotel $hotel
     * @return $this
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * Get hotel
     *
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set search data
     *
     * @param $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * Get search data
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Set record date form
     *
     * @param $date
     */
    public function setRecordDateFrom($date)
    {
        $this->recordDateFrom = $date;
    }

    /**
     * Get record date form
     *
     * @return \DateTime
     */
    public function getRecordDateFrom()
    {
        return $this->recordDateFrom;
    }

    /**
     * Set record date to
     *
     * @param $date
     */
    public function setRecordDateTo($date)
    {
        $this->recordDateTo = $date;
    }

    /**
     * Get record date to
     *
     * @return \DateTime
     */
    public function getRecordDateTo()
    {
        return $this->recordDateTo;
    }

    /**
     * Set operation
     *
     * @param $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    /**
     * Get operation
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set isSystem
     *
     * @param $system
     * @return $this
     */
    public function setIsSystem($system)
    {
        $this->isSystem = $system;

        return $this;
    }

    /**
     * Get isSystem
     *
     * @return bool
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }

    /**
     * Set fullTitle
     *
     * @param $fullTitle
     * @return RecordQuery
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;

        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set record date
     *
     * @param $recordDate
     * @return $this
     */
    public function setRecordDate($recordDate)
    {
        $this->recordDate = $recordDate;

        return $this;
    }

    /**
     * Get record date
     *
     * @return \DateTime
     */
    public function getRecordDate()
    {
        return $this->recordDate;
    }

    /**
     * Set price
     *
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set amount
     *
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount =$amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set qtty
     *
     * @param $qtty
     * @return $this
     */
    public function setQtty($qtty)
    {
        $this->qtty = $qtty;

        return $this;
    }

    /**
     * Get qtty
     *
     * @return float
     */
    public function getQtty()
    {
        return $this->qtty;
    }
}