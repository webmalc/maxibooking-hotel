<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use DateTime;

interface ResultInterface
{
    public function getBegin(): DateTime;

    public function getEnd(): DateTime;

    public function getRoomType(): string;

    public function getTariff(): string;

    public function getPrices(): ?array;

    public function getAdults(): int;

    public function getChildren(): ?int;

    public function getChildrenAges(): array;

}