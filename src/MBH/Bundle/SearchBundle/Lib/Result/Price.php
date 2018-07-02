<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class Price implements \JsonSerializable
{
    /** @var int */
    private $adults;

    /** @var  int*/
    private $children;

    /** @var array */
    private $childrenAges;

    /** @var float */
    private $total;

    /** @var DayPrice[] */
    private $dayPrices;

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return Price
     */
    public function setAdults(int $adults): Price
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
     * @return Price
     */
    public function setChildren(int $children): Price
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     * @return Price
     */
    public function setChildrenAges(array $childrenAges): Price
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return Price
     */
    public function setTotal(float $total): Price
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return DayPrice[]
     */
    public function getDayPrices(): array
    {
        return $this->dayPrices;
    }


    public function addDayPrice(DayPrice $dayPrice): Price
    {
        $this->dayPrices[] = $dayPrice;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            $this->getAdults().'_'.$this->children => [
                'adults' => $this->getAdults(),
                'children' => $this->getChildren(),
                'total' => $this->getTotal(),
                'dayPrices' => $this->getDayPrices()
            ]
        ];
    }


}