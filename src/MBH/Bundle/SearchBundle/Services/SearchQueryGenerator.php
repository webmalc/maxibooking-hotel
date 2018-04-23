<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;


class SearchQueryGenerator
{

    /** @var string */
    private $searchQueryHash;

    /** @var int */
    private $queuesNum;

    /**
     * @param SearchConditions $conditions
     * @throws SearchException
     */
    public function generate(SearchConditions $conditions): void
    {
        $this->searchQueryHash = 'this is must be hash';
        $this->queuesNum = 3;
    }

    /**
     * @return string
     */
    public function getSearchQueryHash(): string
    {
        return $this->searchQueryHash;
    }

    /**
     * @return int
     */
    public function getQueuesNum(): int
    {
        return $this->queuesNum;
    }


}