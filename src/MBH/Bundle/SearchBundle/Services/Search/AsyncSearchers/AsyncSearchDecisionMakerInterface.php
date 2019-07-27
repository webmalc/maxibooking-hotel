<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\SearchConditionsInterface;

interface AsyncSearchDecisionMakerInterface
{
    public function isNeedSearch(SearchConditionsInterface $conditions, QueryGroupInterface $group): bool;

    public function markFoundedResults(SearchConditionsInterface $conditions, QueryGroupInterface $group): void;

    public function canIStoreInStock(SearchConditionsInterface $conditions, QueryGroupInterface $group): bool;

    public function markStoredInStockResult(SearchConditionsInterface $conditions, QueryGroupInterface $group): void;
}