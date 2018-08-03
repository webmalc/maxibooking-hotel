<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchCache implements SearchCacheInterface
{
    public function saveToCache(Result $cacheItem): void
    {
        // TODO: Implement saveToCache() method.
    }

    public function searchInCache(SearchQuery $searchQuery): ?Result
    {
        // TODO: Implement searchInCache() method.
    }

}