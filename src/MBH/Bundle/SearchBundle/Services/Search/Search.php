<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\DayGroupSearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\AsyncQueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupCreator;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;
use MBH\Bundle\SearchBundle\Services\SearchCombinationsGenerator;
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

    /** @var SearchCombinationsGenerator */
    private $combinationsGenerator;

    /** @var ProducerInterface */
    private $producer;

    /** @var FinalSearchResultsAnswerManager */
    private $resultsBuilder;

    /** @var QueryGroupCreator */
    private $queryGroupCreator;

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param SearcherFactory $factory
     * @param DocumentManager $documentManager
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchCombinationsGenerator $queryGenerator
     * @param ProducerInterface $producer
     * @param FinalSearchResultsAnswerManager $builder
     * @param QueryGroupCreator $groupCreator
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearcherFactory $factory,
        DocumentManager $documentManager,
        SearchConditionsCreator $conditionsCreator,
        SearchCombinationsGenerator $queryGenerator,
        ProducerInterface $producer,
        FinalSearchResultsAnswerManager $builder,
        QueryGroupCreator $groupCreator
    )
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcherFactory = $factory;
        $this->dm = $documentManager;
        $this->conditionsCreator = $conditionsCreator;
        $this->combinationsGenerator = $queryGenerator;
        $this->producer = $producer;
        $this->resultsBuilder = $builder;
        $this->queryGroupCreator = $groupCreator;
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
        $searchCombinations = $this->combinationsGenerator->generate($conditions);
        $searchQueries = $searchCombinations->createSearchQueries($conditions);

        $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());

        $results = [];
        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }
        foreach ($searchQueries as $searchQuery) {
            $results[] = $searcher->search($searchQuery);
        }

        $results = $this->resultsBuilder->createAnswer(
            $results,
            $conditions->getErrorLevel(),
            $isCreateJson,
            $isCreateAnswer,
            $grouping
        );


        return $results;
    }


    /**
     * @param array $data
     * @return string
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\QueryGroupException
     */
    public function searchAsync(array $data): string
    {

        $conditions = $this->createSearchConditions($data);
        $searchCombinations = $this->combinationsGenerator->generate($conditions);
        $queryGroups = $this->queryGroupCreator->createQueryGroups($conditions, $searchCombinations, 'QueryGroupByRoomType');

        $conditionsId = $conditions->getId();
        $countQueries = 0;
        /** @var AsyncQueryGroupInterface|QueryGroupInterface $queryGroup */
        foreach ($queryGroups as $queryGroup) {
            $queryGroup->unsetConditions();
            $countQueries += $queryGroup->countQueries();
            $message = [
                'conditionsId' => $conditionsId,
                'searchQueriesGroup' => serialize($queryGroup)
            ];
            $msgBody = json_encode($message);
            $this->producer->publish($msgBody, '', ['priority' => $queryGroup->getQueuePriority()]);
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