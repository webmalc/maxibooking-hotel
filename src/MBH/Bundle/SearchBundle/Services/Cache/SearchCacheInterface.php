<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface SearchCacheInterface
{
    public function searchInCache(SearchQuery $searchQuery): ?Result;

    public function saveToCache(Result $cacheItem, SearchQuery $searchQuery): void;
}