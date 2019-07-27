<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use DateTime;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;

class DayPriceBuilder implements DayPriceBuilderInterface
{

    /** @var DayPrice */
    private $dayPrice;

    public function createInstance(): DayPriceBuilderInterface
    {
        $this->dayPrice = new DayPrice();

        return $this;
    }

    public function setDate(DateTime $date): DayPriceBuilderInterface
    {
        $this->dayPrice->setDate($date);

        return $this;
    }

    public function setAdults(int $adults): DayPriceBuilderInterface
    {
        $this->dayPrice->setAdults($adults);

        return $this;
    }

    public function setChildren(int $children): DayPriceBuilderInterface
    {
        $this->dayPrice->setChildren($children);

        return $this;
    }

    public function setAdditionalAdults(int $addAdults): DayPriceBuilderInterface
    {
        $this->dayPrice->setAdditionalAdults($addAdults);

        return $this;
    }

    public function setAdditionalChildren(int $addChildren): DayPriceBuilderInterface
    {
        $this->dayPrice->setAdditionalChildren($addChildren);

        return $this;
    }

    public function setRoomType(string $roomTypeId): DayPriceBuilderInterface
    {
        $this->dayPrice->setRoomType($roomTypeId);

        return $this;
    }

    public function setTariff(string $tariffId): DayPriceBuilderInterface
    {
        $this->dayPrice->setTariff($tariffId);

        return $this;
    }

    public function setPromotion(string $promotionId): DayPriceBuilderInterface
    {
        $this->dayPrice->setPromotion($promotionId);

        return $this;
    }

    public function setSpecial(string $specialId): DayPriceBuilderInterface
    {
        $this->dayPrice->setSpecial($specialId);

        return $this;
    }

    public function setTotal(float $dayPrice): DayPriceBuilderInterface
    {
        $this->dayPrice->setTotal($dayPrice);

        return $this;
    }

    public function addDiscount(array $discount): DayPriceBuilderInterface
    {
        $this->dayPrice->addDiscount($discount);

        return $this;
    }


    public function getDayPrice(): DayPrice
    {
        $dayPrice = $this->dayPrice;
        unset($this->dayPrice);

        return $dayPrice;
    }


}