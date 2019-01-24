<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface CacheKeyInterface
{
    public function getKey(SearchQuery $searchQuery): string;

    public function getWarmUpKey(SearchQuery $searchQuery): string;

    public function extractWarmUpKey(string $key): array;
}