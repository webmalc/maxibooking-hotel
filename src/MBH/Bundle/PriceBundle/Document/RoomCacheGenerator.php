<?php
/**
 * Created by PhpStorm.
 * Date: 14.09.18
 */

namespace MBH\Bundle\PriceBundle\Document;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;

class RoomCacheGenerator implements \ArrayAccess
{

    /**
     * @var null|\DateTime
     */
    private $begin;

    /**
     * @var null|\DateTime
     */
    private $end;

    /**
     * @var null|array
     */
    private $weekdays;

    /**
     * @var null|\Doctrine\Common\Collections\ArrayCollection
     */
    private $roomTypes;

    /**
     * @var null|\Doctrine\Common\Collections\ArrayCollection
     */
    private $tariffs;

    /**
     * @var null|integer
     */
    private $rooms;

    /**
     * @var bool
     */
    private $isOpen = true;

    /**
     * @var null|bool
     */
    private $quotas;

    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @return int|null
     */
    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    /**
     * @param int|null $rooms
     */
    public function setRooms(?int $rooms): void
    {
        $this->rooms = $rooms;
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
     */
    public function setHotel(Hotel $hotel): void
    {
        $this->hotel = $hotel;
    }

    /**
     * @return bool|null
     */
    public function getQuotas(): ?bool
    {
        return $this->quotas;
    }

    /**
     * @param bool|null $quotas
     */
    public function setQuotas(?bool $quotas): void
    {
        $this->quotas = $quotas;
    }

    /**
     * @return \DateTime|null
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime|null $begin
     */
    public function setBegin(?\DateTime $begin): void
    {
        $this->begin = $begin;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return array|null
     */
    public function getWeekdays(): ?array
    {
        return $this->weekdays;
    }

    /**
     * @param array|null $weekdays
     */
    public function setWeekdays(?array $weekdays): void
    {
        $this->weekdays = $weekdays;
    }

    /**
     * @return array|null
     */
    public function getRoomTypesAsArray(): ?array
    {
        return $this->roomTypes !== null ? $this->roomTypes->toArray() : null;
    }

    /**
     * @param array|null $roomTypes
     */
    public function setRoomTypes(?\Doctrine\Common\Collections\ArrayCollection $roomTypes): void
    {
        $this->roomTypes = $roomTypes;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|null
     */
    public function getRoomTypes(): ?\Doctrine\Common\Collections\ArrayCollection
    {
        return $this->roomTypes;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|null
     */
    public function getTariffs(): ?\Doctrine\Common\Collections\ArrayCollection
    {
        return $this->tariffs;
    }

    /**
     * @return array|null
     */
    public function getTariffsAsArray(): ?array
    {
        return $this->tariffs !== null ? $this->tariffs->toArray() : null;
    }

    /**
     * @param array|null $tariffs
     */
    public function setTariffs(?\Doctrine\Common\Collections\ArrayCollection $tariffs): void
    {
        $this->tariffs = $tariffs;
    }


    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    /**
     * @param bool $isOpen
     */
    public function setIsOpen(bool $isOpen): void
    {
        $this->isOpen = $isOpen;
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->$offset);
        }
    }
}