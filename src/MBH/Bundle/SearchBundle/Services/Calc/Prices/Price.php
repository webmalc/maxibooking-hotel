<?php


namespace MBH\Bundle\SearchBundle\Services\Calc\Prices;


class Price
{
    /** @var int */
    private $adults;

    /** @var int */
    private $children = 0;

    /** @var array */
    private $childrenAges = [];

    /** @var int */
    private $infants = 0;

    /** @var DayPrice[] */
    private $priceByDay = [];

    private $total;

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
     * @return int
     */
    public function getInfants(): int
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     * @return Price
     */
    public function setInfants(int $infants): Price
    {
        $this->infants = $infants;

        return $this;
    }

    public function setTotal($total): Price
    {
        $this->total = $total;

        return $this;
    }

    public function getTotal()
    {
        $total = 0;
        if (is_array($this->priceByDay)) {
            foreach ($this->priceByDay as $dayPrice) {
                $total += $dayPrice->getTotal();
            }
        }

        return $total;
    }

    /**
     * @return DayPrice[]
     */
    public function getPriceByDay(): array
    {
        return $this->priceByDay;
    }

    /**
     * @param DayPrice $priceByDay
     * @return Price
     */
    public function addPriceByDay(DayPrice $priceByDay): Price
    {
        $this->priceByDay[] = $priceByDay;

        return $this;
    }

    public function setPriceByDay(array $pricesByDay): Price
    {
        $this->priceByDay = $pricesByDay;

        return $this;
    }


}