<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;
use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\SearchConditionsInterface;

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
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @param string $grouperName
     * @param SearchConditionsInterface|null $conditions
     * @param bool $separated
     * @return mixed
     * @throws GroupingFactoryException
     */
    public function createAnswer($results, int $errorLevel, bool $isCreateJson, bool $isCreateAnswer, string $grouperName = null, ?string $searchConditionHash = null, bool $separated = true)
    {

        $this->builder
            ->set($results)
            ->errorFilter($errorLevel)
            ->setGrouping($grouperName)
            ->createJson($isCreateJson)
            ->createAnswer($isCreateAnswer);
        if ($searchConditionHash) {
            $this->builder->setSearchHashConditions($searchConditionHash);
        }

        return $this->builder->getResults($separated);
    }
}