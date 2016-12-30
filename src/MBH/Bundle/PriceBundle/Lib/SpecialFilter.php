<?php

namespace MBH\Bundle\PriceBundle\Lib;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * Class SpecialFilter
 * @package MBH\Bundle\PriceBundle\Lib
 */
class SpecialFilter
{
    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var Tariff
     */
    private $tariff;

    /**
     * @var RoomType
     */
    private $roomType;

    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return SpecialFilter
     */
    public function setBegin(\DateTime $begin = null): SpecialFilter
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return SpecialFilter
     */
    public function setEnd(\DateTime $end = null): SpecialFilter
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return SpecialFilter
     */
    public function setIsEnabled(bool $isEnabled = null): SpecialFilter
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): ?Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return SpecialFilter
     */
    public function setTariff(Tariff $tariff = null): SpecialFilter
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return SpecialFilter
     */
    public function setRoomType(RoomType $roomType = null): SpecialFilter
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return SpecialFilter
     */
    public function setHotel(Hotel $hotel): SpecialFilter
    {
        $this->hotel = $hotel;
        return $this;
    }
}