<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataQueryInterface;

interface CalcQueryInterface extends DataQueryInterface
{
    public function getAdults(): int;

    public function getChildren(): ?int;

    public function getChildrenAges(): array;

    public function setTariffId(string $tariffId);

    public function getSpecialId(): ?string;
}