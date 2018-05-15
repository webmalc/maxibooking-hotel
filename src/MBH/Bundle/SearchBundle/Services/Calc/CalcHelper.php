<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;

class CalcHelper
{
    /** @var \DateTime */
    private $searchBegin;

    /** @var \DateTime */
    private $searchEnd;

    /** @var RoomType */
    private $roomType;

    /** @var Tariff */
    private $tariff;

    /** @var int */
    private $actualAdults = 0;

    /** @var int */
    private $actualChildren = 0;

    /** @var Promotion */
    private $promotion;

    /** @var Special */
    private $special;

    /** @var bool */
    private $isStrictDuration = true;

    /** @var bool */
    private $isUseCategory;


    /**
     * @return \DateTime
     */
    public function getSearchBegin(): \DateTime
    {
        return $this->searchBegin;
    }

    /**
     * @param \DateTime $searchBegin
     * @return CalcHelper
     */
    public function setSearchBegin(\DateTime $searchBegin): CalcHelper
    {
        $this->searchBegin = $searchBegin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSearchEnd(): \DateTime
    {
        return $this->searchEnd;
    }

    /**
     * @param \DateTime $searchEnd
     * @return CalcHelper
     */
    public function setSearchEnd(\DateTime $searchEnd): CalcHelper
    {
        $this->searchEnd = $searchEnd;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return CalcHelper
     */
    public function setRoomType(RoomType $roomType): CalcHelper
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return CalcHelper
     */
    public function setTariff(Tariff $tariff): CalcHelper
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return int
     */
    public function getActualAdults(): int
    {
        return $this->actualAdults;
    }

    /**
     * @param int $actualAdults
     * @return CalcHelper
     */
    public function setActualAdults(int $actualAdults): CalcHelper
    {
        $this->actualAdults = $actualAdults;

        return $this;
    }

    /**
     * @return int
     */
    public function getActualChildren(): int
    {
        return $this->actualChildren;
    }

    /**
     * @param int $actualChildren
     * @return CalcHelper
     */
    public function setActualChildren(int $actualChildren): CalcHelper
    {
        $this->actualChildren = $actualChildren;

        return $this;
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
     * @return CalcHelper
     */
    public function setPromotion(Promotion $promotion): CalcHelper
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return CalcHelper
     */
    public function setSpecial(Special $special): CalcHelper
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStrictDuration(): bool
    {
        return $this->isStrictDuration;
    }

    /**
     * @param bool $isStrictDuration
     * @return CalcHelper
     */
    public function setIsStrictDuration(bool $isStrictDuration): CalcHelper
    {
        $this->isStrictDuration = $isStrictDuration;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseCategory(): bool
    {
        return $this->isUseCategory;
    }

    /**
     * @param bool $isUseCategory
     * @return CalcHelper
     */
    public function setIsUseCategory(bool $isUseCategory): CalcHelper
    {
        $this->isUseCategory = $isUseCategory;

        return $this;
    }



    public function getDuration(): int
    {
        return (int)$this->searchEnd->diff($this->searchBegin)->format('%a');
    }

    public function getPriceCacheEnd(): \DateTime
    {
        return (clone $this->searchEnd)->modify('-1 day');
    }

    public function getPriceTariffId(): string
    {
        if ($this->tariff->getParent() && $this->tariff->getChildOptions()->isInheritPrices()) {
            return $this->tariff->getParent()->getId();
        }

        return $this->tariff->getId();
    }

    public function getPriceRoomTypeId(): string
    {
        if ($this->isUseCategory) {
            if (null === $this->roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $this->roomType->getCategory()->getId();
        }

        return $this->roomType->getId();
    }

    public function isChildPrices(): bool
    {
        if ($this->isUseCategory) {
            if (null === $this->roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $this->roomType->getCategory()->getIsChildPrices();
        }

        return $this->roomType->getIsChildPrices();
    }

    public function isIndividualAdditionalPrices(): bool
    {
        if ($this->isUseCategory) {
            if (null === $this->roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $this->roomType->getCategory()->getIsIndividualAdditionalPrices();
        }

        return $this->roomType->getIsIndividualAdditionalPrices();
    }




}