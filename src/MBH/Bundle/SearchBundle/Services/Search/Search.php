<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\DayGroupSearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class Search
{

    /** @var bool */
    public const PRE_RESTRICTION_CHECK = false;

    /** @var SearcherFactory */
    private $searcherFactory;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var DocumentManager */
    private $dm;

    /** @var SearchConditionsCreator */
    private $conditionsCreator;

    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /** @var ProducerInterface */
    private $producer;

    /** @var FinalSearchResultsBuilder */
    private $resultsBuilder;

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param SearcherFactory $factory
     * @param DocumentManager $documentManager
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchQueryGenerator $queryGenerator
     * @param ProducerInterface $producer
     * @param FinalSearchResultsBuilder $builder
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearcherFactory $factory,
        DocumentManager $documentManager,
        SearchConditionsCreator $conditionsCreator,
        SearchQueryGenerator $queryGenerator,
        ProducerInterface $producer,
        FinalSearchResultsBuilder $builder
    )
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcherFactory = $factory;
        $this->dm = $documentManager;
        $this->conditionsCreator = $conditionsCreator;
        $this->queryGenerator = $queryGenerator;
        $this->producer = $producer;
        $this->resultsBuilder = $builder;
    }


    /**
     * @param array $data
     * @param null $grouping
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @return mixed
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function searchSync(
        array $data,
        $grouping = null,
        bool $isCreateJson = false,
        bool $isCreateAnswer = false
    )
    {
        $conditions = $this->createSearchConditions($data);
        $searchQueries = $this->queryGenerator->generate($conditions, false);
        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }
        $results = [];
        $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());
        foreach ($searchQueries as $searchQuery) {
            $results[] = $searcher->search($searchQuery);
        }

        $results = $this->resultsBuilder
            ->set($results)
            ->errorFilter($conditions->getErrorLevel())
            ->setGrouping($grouping)
            ->createJson($isCreateJson)
            ->createAnswer($isCreateAnswer)
            ->getResults();

        return $results;
    }


    /**
     * @param array $data
     * @return string
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function searchAsync(array $data): string
    {

        $conditions = $this->createSearchConditions($data);
        $dayGroupedSearchQueries = $this->queryGenerator->generate($conditions, true);
        //** TODO: Create SearchQueryGroup */

        $conditionsId = $conditions->getId();
        $countQueries = 0;
        foreach ($dayGroupedSearchQueries as $groupSearchQuery) {
            $queries = [];
            /** @var DayGroupSearchQuery $groupSearchQuery */
            foreach ($groupSearchQuery->getSearchQueries() as $searchQuery) {
                /** @var SearchQuery $searchQuery */
                $searchQuery->unsetConditions();
                $queries[] = $searchQuery;
                $countQueries++;
            }
            $message = [
                'conditionsId' => $conditionsId,
                'searchQueries' => serialize($queries)
            ];
            $msgBody = json_encode($message);
            $this->producer->publish($msgBody, '', ['priority' => $groupSearchQuery->getType() === DayGroupSearchQuery::MAIN_DATES ? 10: 1]);
        }

        $conditions->setExpectedResultsCount($countQueries);
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);

        return $conditions->getId();
    }


    /**
     * @param array $data
     * @return SearchConditions
     * @throws SearchConditionException
     */
    private function createSearchConditions(array $data): SearchConditions
    {

        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $this->saveQueryStat($conditions);

        return $conditions;
    }

    /**
     * @param SearchConditions $conditions
     */
    private function saveQueryStat(SearchConditions $conditions): void
    {
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);
    }

}