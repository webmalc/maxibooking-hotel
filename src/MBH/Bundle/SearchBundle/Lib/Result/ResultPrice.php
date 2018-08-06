<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class ResultPrice implements \JsonSerializable
{
    /** @var int */
    private $searchAdults;

    /** @var  int*/
    private $searchChildren;

    /** @var array */
    private $childrenAges;

    /** @var float */
    private $total;

    /** @var ResultDayPrice[] */
    private $dayPrices = [];

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
     * @return array
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     * @return ResultPrice
     */
    public function setChildrenAges(array $childrenAges): ResultPrice
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

    public function jsonSerialize()
    {
        return [
                'adults' => $this->getSearchAdults(),
                'children' => $this->getSearchChildren(),
                'total' => $this->getTotal(),
                'dayPrices' => $this->getDayPrices()
        ];
    }

    public static function createInstance(int $searchAdults, int $searchChildren, int $total, array $dayPrices = []): ResultPrice
    {
        $resultPrice = new self();
        $resultPrice
            ->setSearchAdults($searchAdults)
            ->setSearchChildren($searchChildren)
            ->setTotal($total)
            ->setDayPrices($dayPrices)
        ;

        return $resultPrice;
    }

}