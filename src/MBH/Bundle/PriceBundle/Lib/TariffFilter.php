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
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var boolean
     */
    private $isOnline;

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
     * @return int|null
     */
    public function getIsEnabled(): ?int
    {
        return $this->isEnabled;
    }

    /**
     * @param int $isEnabled
     * @return TariffFilter
     */
    public function setIsEnabled($isEnabled = null): TariffFilter
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIsOnline(): ?int
    {
        return $this->isOnline;
    }

    /**
     * @param int $isOnline
     * @return TariffFilter
     */
    public function setIsOnline($isOnline = null): TariffFilter
    {
        $this->isOnline = $isOnline;
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


}