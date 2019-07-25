<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result\Builder;


use DateTime;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

interface ResultBuilderInterface
{
    public function createInstance(): ResultBuilderInterface;

    public function addBegin(DateTime $begin): ResultBuilderInterface;

    public function addEnd(DateTime $end): ResultBuilderInterface;

    public function addRoomType(string $roomTypeId): ResultBuilderInterface;

    public function addRoomTypeCategory(string $categoryId): ResultBuilderInterface;

    public function addTariff(string $tariffId): ResultBuilderInterface;

    public function addPrices(array $prices): ResultBuilderInterface;

    public function setOkStatus(): ResultBuilderInterface;

    public function setErrorStatus(string $message, int $errorCode): ResultBuilderInterface;

    public function addRoomAvailableAmount(int $amount): ResultBuilderInterface;

    public function getResult(): Result;

    public function addAdults(int $adults): ResultBuilderInterface;

    public function addChildren(int $children): ResultBuilderInterface;

    public function addChildrenAges(array $childrenAges): ResultBuilderInterface;
}