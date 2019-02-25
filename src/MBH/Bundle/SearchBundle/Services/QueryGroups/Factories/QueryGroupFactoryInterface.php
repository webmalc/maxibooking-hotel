<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups\Factories;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\Search\SearchCombinations;

interface QueryGroupFactoryInterface
{
    public function createQueryGroups(SearchConditions $searchConditions, SearchCombinations $combinations): array;
}