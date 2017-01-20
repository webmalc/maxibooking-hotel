<?php

namespace MBH\Bundle\PriceBundle\Lib;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * Class TariffFilter
 * @package MBH\Bundle\PriceBundle\Lib
 */
class TariffFilter
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
     * @var int
     */
    private $remain = null;

    /**
     * @var Special
     */
    private $excludeSpecial;

    private $isOnline;

    /**
     * @return bool
     */
    public function getIsOnline(): ?bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     * @return TariffFilter
     */
    public function setIsOnline(bool $isEnabled = null): TariffFilter
    {
        $this->isEnabled = $isEnabled;
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
     * @return TariffFilter
     */
    public function setBegin(\DateTime $begin = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setEnd(\DateTime $end = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setIsEnabled(bool $isEnabled = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setTariff(Tariff $tariff = null): TariffFilter
    {
        $this->tariff = $tariff;
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
     * @return TariffFilter
     */
    public function setHotel(Hotel $hotel): TariffFilter
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
     * @return TariffFilter
     */
    public function setRemain(int $remain = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setExcludeSpecial(Special $excludeSpecial = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setDisplayFrom(\DateTime $displayFrom = null): TariffFilter
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
     * @return TariffFilter
     */
    public function setDisplayTo(\DateTime $displayTo = null): TariffFilter
    {
        $this->displayTo = $displayTo;
        return $this;
    }


}