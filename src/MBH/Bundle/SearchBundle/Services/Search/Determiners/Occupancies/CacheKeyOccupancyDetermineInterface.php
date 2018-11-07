<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

interface CacheKeyOccupancyDetermineInterface
{
    public function determine(SearchQuery $searchQuery, string $type): OccupancyInterface;
}