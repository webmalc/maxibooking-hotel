<?php

namespace MBH\Bundle\PriceBundle\Lib;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Special;
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
     * @var \DateTime
     */
    private $displayFrom;

    /**
     * @var \DateTime
     */
    private $displayTo;

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
     * @var int
     */
    private $remain = null;

    /**
     * @var Special
     */
    private $excludeSpecial;

    /** @var bool  */
    private $showDeleted = false;

    /**
     * @return bool
     */
    public function showDeleted(): ?bool
    {
        return $this->showDeleted;
    }

    /**
     * @param bool $showDeleted
     * @return SpecialFilter
     */
    public function setShowDeleted(bool $showDeleted): SpecialFilter
    {
        $this->showDeleted = $showDeleted;

        return $this;
    }

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
    public function getHotel(): ?Hotel
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

    /**
     * @return int
     */
    public function getRemain(): ?int
    {
        return $this->remain;
    }

    /**
     * @param int $remain
     * @return SpecialFilter
     */
    public function setRemain(int $remain = null): SpecialFilter
    {
        $this->remain = $remain;
        return $this;
    }

    /**
     * @return Special
     */
    public function getExcludeSpecial(): ?Special
    {
        return $this->excludeSpecial;
    }

    /**
     * @param Special $excludeSpecial
     * @return SpecialFilter
     */
    public function setExcludeSpecial(Special $excludeSpecial = null): SpecialFilter
    {
        $this->excludeSpecial = $excludeSpecial;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDisplayFrom(): ?\DateTime
    {
        return $this->displayFrom;
    }

    /**
     * @param \DateTime $displayFrom
     * @return SpecialFilter
     */
    public function setDisplayFrom(\DateTime $displayFrom = null): SpecialFilter
    {
        $this->displayFrom = $displayFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDisplayTo(): ?\DateTime
    {
        return $this->displayTo;
    }

    /**
     * @param \DateTime $displayTo
     * @return SpecialFilter
     */
    public function setDisplayTo(\DateTime $displayTo = null): SpecialFilter
    {
        $this->displayTo = $displayTo;
        return $this;
    }


}