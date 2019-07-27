<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners;


interface CalcOccupancyInterface
{
    public function getMainAdults(): int;

    public function getMainChildren(): int;

    public function getAddAdults(): int;

    public function getAddChildren(): int;
}