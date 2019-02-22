<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups\Factories;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

interface QueryGroupFactoryInterface
{
    public function createQueryGroups(SearchConditions $searchConditions, array $dates, array $combinations): array;
}