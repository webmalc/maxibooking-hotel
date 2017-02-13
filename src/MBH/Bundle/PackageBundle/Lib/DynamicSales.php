<?php

namespace MBH\Bundle\PackageBundle\Lib;


use MBH\Bundle\HotelBundle\Document\RoomType;


class DynamicSales
{
    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var array
     */
    protected $periods;

    /**
     * @var array
     */
    protected $comparison;

    /**
     * @return array
     */
    public function getComparison():? array
    {
        return $this->comparison;
    }

    /**
     * @param array $comparison
     */
    public function addComparison(array $comparison)
    {
        $this->comparison[] = $comparison;
    }

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @param array $periods
     */
    public function addPeriods(array $periods)
    {
        $this->periods[] = $periods;
    }

    /**
     * @return mixed
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param mixed $roomType
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;
    }
}