<?php


namespace MBH\Bundle\SearchBundle\Services\QueryGroups;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\Traits\UnsetConditionTrait;

class QueryGroupByDay implements QueryGroupInterface, AsyncQueryGroupInterface
{

    use UnsetConditionTrait;
    
    public const NAME = 'dayGroup';

    private $priority = 1;

    /** @var SearchQuery[] */
    private $queries;


    public function setSearchQueries(array $queries): QueryGroupByDay
    {
        $this->queries = $queries;

        return $this;
    }

    public function getSearchQueries(): array
    {
        return $this->queries;
    }


    public function getGroupName(): string
    {
        return self::NAME;
    }

    public function getQueuePriority(): int
    {
        return $this->priority;
    }


}