<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;

interface AsyncSearcherInterface
{
    public function search(string $conditionsId, QueryGroupInterface $searchQueryGroup): void;
}