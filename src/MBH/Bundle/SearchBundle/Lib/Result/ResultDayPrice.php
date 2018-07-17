<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class ResultDayPrice implements \JsonSerializable
{
    /** @var \DateTime */
    private $date;

    /** @var ResultTariff */
    private $tariff;

    /** @var float */
    private $price;

    /** @var int */
    private $adults;

    /** @var int */
    private $children;

    /** @var int */
    private $infants;

    /** @var ResultPromotion */
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
     * @return ResultDayPrice
     */
    public function setDate(\DateTime $date): ResultDayPrice
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return ResultTariff
     */
    public function getTariff(): ResultTariff
    {
        return $this->tariff;
    }

    /**
     * @param ResultTariff $tariff
     * @return ResultDayPrice
     */
    public function setTariff(ResultTariff $tariff): ResultDayPrice
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
     * @return ResultDayPrice
     */
    public function setPrice(float $price): ResultDayPrice
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
     * @return ResultDayPrice
     */
    public function setAdults(int $adults): ResultDayPrice
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
     * @return ResultDayPrice
     */
    public function setChildren(int $children): ResultDayPrice
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
     * @return ResultDayPrice
     */
    public function setInfants(int $infants): ResultDayPrice
    {
        $this->infants = $infants;

        return $this;
    }

    /**
     * @return ResultPromotion
     */
    public function getPromotion(): ?ResultPromotion
    {
        return $this->promotion;
    }

    /**
     * @param ResultPromotion $promotion
     * @return ResultDayPrice
     */
    public function setPromotion(ResultPromotion $promotion): ResultDayPrice
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'day' => $this->getDate()->format('d.m.Y'),
            'tariff' => $this->getTariff(),
            'price' => $this->getPrice(),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'infants' => $this->getInfants(),
            'promotion' => $this->getPromotion()
        ];
    }


}