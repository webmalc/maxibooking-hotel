<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class DayPrice implements \JsonSerializable
{
    /** @var \DateTime */
    private $date;

    /** @var Tariff */
    private $tariff;

    /** @var float */
    private $price;

    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var int */
    private $infants;

    /** @var Promotion */
    private $promotion;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return DayPrice
     */
    public function setDate(\DateTime $date): DayPrice
    {
        $this->date = $date;

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
     * @return DayPrice
     */
    public function setTariff(Tariff $tariff): DayPrice
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return DayPrice
     */
    public function setPrice(float $price): DayPrice
    {
        $this->price = $price;

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
    public function getInfants(): int
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     * @return DayPrice
     */
    public function setInfants(int $infants): DayPrice
    {
        $this->infants = $infants;

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
     * @return DayPrice
     */
    public function setPromotion(Promotion $promotion): DayPrice
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'day' => $this->getDate()->format('dd-mm-YY'),
            'tariff' => $this->getTariff(),
            'price' => $this->getPrice(),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'infants' => $this->getInfants(),
            'promotion' => $this->getPromotion()
        ];
    }


}