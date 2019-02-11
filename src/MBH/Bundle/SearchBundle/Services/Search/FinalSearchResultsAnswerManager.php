<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;

/**
 * Class FinalSearchResultsAnswerManager
 * @package MBH\Bundle\SearchBundle\Services\Search
 */
class FinalSearchResultsAnswerManager
{

    /** @var FinalSearchResultsBuilder */
    private $builder;

    /**
     * FinalSearchResultsAnswerManager constructor.
     * @param FinalSearchResultsBuilder $builder
     */
    public function __construct(FinalSearchResultsBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param $results
     * @param int $errorLevel
     * @param string $grouping
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @return mixed
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function createAnswer($results, int $errorLevel, string $grouping, bool $isCreateJson, bool $isCreateAnswer)
    {
        return $this->builder
            ->set($results)
            ->errorFilter($errorLevel)
            ->setGrouping($grouping)
            ->createJson($isCreateJson)
            ->createAnswer($isCreateAnswer)
            ->getResults();
    }
}