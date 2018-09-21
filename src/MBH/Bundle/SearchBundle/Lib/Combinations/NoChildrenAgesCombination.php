<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations;


class NoChildrenAgesCombination extends AbstractCombinations
{
    public function getCombinations(): array
    {
        return $this->getPlacesCombinations();
    }

}