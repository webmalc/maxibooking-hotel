<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations;


interface CombinationInterface
{
    public function getTariffIds(): array;

    public function getCombinations(): array;
}