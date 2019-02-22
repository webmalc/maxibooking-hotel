<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface QueryGroupInterface
{
    /** @return SearchQuery[] */
    public function getSearchQueries(): array;

    public function getGroupName(): string;

    public function getQueuePriority(): int;
}