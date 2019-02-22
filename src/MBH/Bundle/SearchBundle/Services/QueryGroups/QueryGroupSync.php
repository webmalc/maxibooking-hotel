<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class QueryGroupSync implements QueryGroupInterface
{
    public const NAME = 'syncGroup';

    /** @var SearchQuery[] */
    private $searchQueries;

    public function setSearchQueries(array $searchQueries): void
    {
        $this->searchQueries = $searchQueries;
    }

    /** @return SearchQuery[] */
    public function getSearchQueries(): array
    {
        return $this->searchQueries;
    }

    public function getQueuePriority(): int
    {
        return 1;
    }

    public function getGroupName(): string
    {
        return self::NAME;
    }


}