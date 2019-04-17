<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class ResultPrice
{
    /** @var int */
    private $searchAdults;

    /** @var  int*/
    private $searchChildren;

    /** @var float */
    private $total;

    /** @var ResultDayPrice[] */
    private $dayPrices = [];

    /** @var int */
    private $discount;

    /**
     * @return int
     */
    public function getSearchAdults(): int
    {
        return $this->searchAdults;
    }

    /**
     * @param int $searchAdults
     * @return ResultPrice
     */
    public function setSearchAdults(int $searchAdults): ResultPrice
    {
        $this->searchAdults = $searchAdults;

        return $this;
    }

    /**
     * @return int
     */
    public function getSearchChildren(): int
    {
        return $this->searchChildren;
    }

    /**
     * @param int $searchChildren
     * @return ResultPrice
     */
    public function setSearchChildren(int $searchChildren): ResultPrice
    {
        $this->searchChildren = $searchChildren;

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
     * @return ResultPrice
     */
    public function setTotal(float $total): ResultPrice
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return ResultDayPrice[]
     */
    public function getDayPrices(): array
    {
        return $this->dayPrices;
    }

    /**
     * @param ResultDayPrice[] $dayPrices
     * @return ResultPrice
     */
    public function setDayPrices(array $dayPrices): ResultPrice
    {
        $this->dayPrices = $dayPrices;

        return $this;
    }


    public function addDayPrice(ResultDayPrice $dayPrice): ResultPrice
    {
        $this->dayPrices[] = $dayPrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     * @return ResultPrice
     */
    public function setDiscount(?int $discount): ResultPrice
    {
        $this->discount = $discount;

        return $this;
    }




    public static function createInstance(int $searchAdults, int $searchChildren, int $total, array $dayPrices = [], ?int $discount = null): ResultPrice
    {
        $resultPrice = new self();
        $resultPrice
            ->setSearchAdults($searchAdults)
            ->setSearchChildren($searchChildren)
            ->setTotal($total)
            ->setDayPrices($dayPrices)
            ->setDiscount($discount)
        ;

        return $resultPrice;
    }

}