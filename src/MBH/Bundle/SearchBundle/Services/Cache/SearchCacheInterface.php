<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface SearchCacheInterface
{
    public function searchInCache(SearchQuery $searchQuery, $hydrated = true);

    public function saveToCache(Result $result, SearchQuery $searchQuery): void;
}