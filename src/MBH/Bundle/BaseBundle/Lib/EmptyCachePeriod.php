<?php

namespace MBH\Bundle\BaseBundle\Lib;

use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;

class EmptyCachePeriod
{
    const DATE_FORMAT = 'd.m.Y';
    /** @var \DateTime */
    private $begin;
    /** @var \DateTime */
    private $end;
    /** @var Tariff */
    private $tariff;
    /** @var RoomType */
    private $roomType;

    public function __construct(\DateTime $begin, \DateTime $end, RoomType $roomType, Tariff $tariff = null) {
        $this->begin = $begin;
        $this->end = $end;
        $this->tariff = $tariff;
        $this->roomType = $roomType;
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
     * @return EmptyCachePeriod
     */
    public function setBegin(\DateTime $begin): EmptyCachePeriod
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
     * @return EmptyCachePeriod
     */
    public function setEnd(\DateTime $end): EmptyCachePeriod
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getPeriodAsString()
    {
        return $this->begin->format(self::DATE_FORMAT) . ' - ' . $this->end->format(self::DATE_FORMAT);
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
     * @return EmptyCachePeriod
     */
    public function setTariff(Tariff $tariff): EmptyCachePeriod
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
     * @return EmptyCachePeriod
     */
    public function setRoomType(RoomType $roomType): EmptyCachePeriod
    {
        $this->roomType = $roomType;

        return $this;
    }
}