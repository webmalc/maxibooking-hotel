<?php namespace MBH\Bundle\WarehouseBundle\Lib;

use MBH\Bundle\WarehouseBundle\Document\WareItem;

class RecordQuery
{
    public $isSystem;

    public $fullTitle;

    public $recordDate;

    public $recordDateFrom;

    public $recordDateTo;

    public $operation;

    public $hotel;

    public $wareItem;

    public $price;

    public $qtty;

    public $amount;

    public $sortBy;

    public $sortDirection;

    public $id;

    public $isEnabled;

    public $search;

    public function getSortDirection() {
        return $this->sortDirection;
    }

    public function setSortDirection($param = null) {
        $this->sortDirection = $param;

        return $this;
    }

    public function setSortBy($sortBy)
    {
        $this->fullTitle = $sortBy;

        return $this;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setWareItem($item)
    {
        $this->wareItem = $item;

        return $this;
    }

    public function getWareItem()
    {
        return $this->wareItem;
    }

    public function getHotel()
    {
        return $this->hotel;
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setRecordDateFrom($date)
    {
        $this->recordDateFrom = $date;
    }

    public function getRecordDateFrom()
    {
        return $this->recordDateFrom;
    }

    public function setRecordDateTo($date)
    {
        $this->recordDateTo = $date;
    }

    public function getRecordDateTo()
    {
        return $this->recordDateTo;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    public function getOperation()
    {
        return $this->operation;
    }

}