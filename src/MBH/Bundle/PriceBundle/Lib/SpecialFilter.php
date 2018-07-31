<?php

namespace MBH\Bundle\PriceBundle\Lib;
use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;

/**
 * Class SpecialFilter
 * @package MBH\Bundle\PriceBundle\Lib
 */
class SpecialFilter
{

    //TODO: Костыль!!! Вынести хотя бы в настройки
    const SPECIAL_INFANT_AGE = 2;
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

    /**
     * @var bool
     */
    private $isStrict;
    /**
     * @return \DateTime
     */

    /**
     * @var int
     */
    private $adults;
    /**
     * @var int
     */
    private $children = 0;
    /**
     * @var array
     */
    private $childrenAges = [];

    /**
     * @var int
     */
    private $infantAge = self::SPECIAL_INFANT_AGE;

    /**
     * @var array
     */
    private $roomTypes;

    /** @var Promotion */
    private $promotion;



    /**
     * @return \DateTime|null
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

    /**
     * @return bool
     */
    public function isStrict(): ?bool
    {
        return $this->isStrict;
    }

    /**
     * @param bool $isStrict
     * @return $this
     */
    public function setIsStrict(bool $isStrict)
    {
        $this->isStrict = $isStrict;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     */
    public function setAdults(int $adults)
    {
        $this->adults = $adults;
    }

    /**
     * @return int
     */
    public function getChildren(): ?int
    {
        return $this->children;
    }

    /**
     * @param int $children
     */
    public function setChildren(int $children)
    {
        $this->children = $children;
    }

    /**
     * @return array
     */
    public function getChildrenAges(): ?array
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     */
    public function setChildrenAges(array $childrenAges)
    {
        $this->childrenAges = $childrenAges;
    }

    /**
     * @return int
     */
    public function getInfantAge(): ?int
    {
        return $this->infantAge;
    }

    /**
     * @param mixed $infantAge
     */
    public function setInfantAge(int $infantAge)
    {
        $this->infantAge = $infantAge;
    }

    /**
     * @return array
     */
    public function getRoomTypes(): ?array
    {
        return $this->roomTypes;
    }

    /**
     * @param array $roomTypes
     */
    public function setRoomTypes(array $roomTypes)
    {
        $this->roomTypes = $roomTypes;
    }

    public function addRoomType($roomType)
    {
        $this->roomTypes[] = $roomType;
    }

    /**
     * @return Promotion
     */
    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    /**
     * @param Promotion $promotion
     */
    public function setPromotion(Promotion $promotion = null): void
    {
        $this->promotion = $promotion;
    }







}