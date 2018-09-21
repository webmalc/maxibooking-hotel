<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface CacheKeyInterface
{
    public function getKey(SearchQuery $searchQuery): string;
}