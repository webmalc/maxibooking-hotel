<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;

interface AsyncSearchDecisionMakerInterface
{
    public function isNeedSearch(SearchConditions $conditions, QueryGroupInterface $group): bool;

    public function markFoundedResults(SearchConditions $conditions, QueryGroupInterface $group, bool $isFounded): void;
}