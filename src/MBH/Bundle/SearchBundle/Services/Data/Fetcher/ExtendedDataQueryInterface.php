<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;

interface ExtendedDataQueryInterface
{
    public function getBegin(): DateTime;

    public function getEnd(): DateTime;

    public function getTariffs(): ?iterable;

    public function getRoomTypes(): ?iterable;

    public function getHotels(): ?iterable;
}