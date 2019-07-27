<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use DateTime;
use MBH\Bundle\SearchBundle\Services\Calc\Prices\DayPrice;

interface DayPriceBuilderInterface
{
    public function createInstance(): DayPriceBuilderInterface;

    public function setDate(DateTime $date): DayPriceBuilderInterface;

    public function setAdults(int $adults): DayPriceBuilderInterface;

    public function setChildren(int $children): DayPriceBuilderInterface;

    public function setAdditionalAdults(int $addAdults): DayPriceBuilderInterface;

    public function setAdditionalChildren(int $addChildren): DayPriceBuilderInterface;

    public function setRoomType(string $roomTypeId): DayPriceBuilderInterface;

    public function setTariff(string $tariffId): DayPriceBuilderInterface;

    public function setPromotion(string $promotionId): DayPriceBuilderInterface;

    public function setSpecial(string $specialId): DayPriceBuilderInterface;

    public function setTotal(float $dayPrice): DayPriceBuilderInterface;

    public function addDiscount(array $discount): DayPriceBuilderInterface;

    public function getDayPrice(): DayPrice;

}