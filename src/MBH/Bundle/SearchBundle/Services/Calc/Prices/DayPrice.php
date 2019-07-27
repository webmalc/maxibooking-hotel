<?php


namespace MBH\Bundle\SearchBundle\Services\Calc\Prices;


use DateTime;

class DayPrice
{
    /** @var DateTime */
    private $date;

    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var int */
    private $additionalAdults;

    /** @var int */
    private $additionalChildren;

    /** @var float */
    private $total;

    /** @var string */
    private $roomType;

    /** @var string */
    private $tariff;

    /** @var string */
    private $promotion;

    /** @var array */
    private $discount = [];

    /** @var string */
    private $special;

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return DayPrice
     */
    public function setDate(DateTime $date): DayPrice
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return DayPrice
     */
    public function setAdults(int $adults): DayPrice
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return DayPrice
     */
    public function setChildren(int $children): DayPrice
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalAdults(): int
    {
        return $this->additionalAdults;
    }

    /**
     * @param int $additionalAdults
     * @return DayPrice
     */
    public function setAdditionalAdults(int $additionalAdults): DayPrice
    {
        $this->additionalAdults = $additionalAdults;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalChildren(): int
    {
        return $this->additionalChildren;
    }

    /**
     * @param int $additionalChildren
     * @return DayPrice
     */
    public function setAdditionalChildren(int $additionalChildren): DayPrice
    {
        $this->additionalChildren = $additionalChildren;

        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return DayPrice
     */
    public function setTotal($total): DayPrice
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomType(): string
    {
        return $this->roomType;
    }

    /**
     * @param string $roomType
     * @return DayPrice
     */
    public function setRoomType(string $roomType): DayPrice
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTariff(): string
    {
        return $this->tariff;
    }

    /**
     * @param string $tariff
     * @return DayPrice
     */
    public function setTariff(string $tariff): DayPrice
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return string
     */
    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    /**
     * @param string $promotion
     * @return DayPrice
     */
    public function setPromotion(?string $promotion): DayPrice
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return array
     */
    public function getDiscount(): array
    {
        return $this->discount;
    }

    /**
     * @param array $discount
     * @return DayPrice
     */
    public function addDiscount(array $discount): DayPrice
    {
        $this->discount[] = $discount;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpecial(): ?string
    {
        return $this->special;
    }

    /**
     * @param string $special
     * @return DayPrice
     */
    public function setSpecial(?string $special): DayPrice
    {
        $this->special = $special;

        return $this;
    }


}