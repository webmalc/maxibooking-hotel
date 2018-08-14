<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class Search
{
    /** @var int */
    public const QUERIES_CHUNK_NUM = 20;

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

    /** @var int */
    private $asyncQueriesChunk;

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
     * @param bool $isHideError
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
        bool $isHideError = true,
        $grouping = null,
        bool $isCreateJson = false,
        bool $isCreateAnswer = false
    )
    {
        $conditions = $this->createSearchConditions($data);
        $searchQueries = $this->queryGenerator->generateSearchQueries($conditions);
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
            ->hideError($isHideError)
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
        $searchQueries = $this->queryGenerator->generateSearchQueries($conditions);
        $conditions->setExpectedResultsCount(\count($searchQueries));
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);

        $searchQueriesChunks = array_chunk($searchQueries, $this->getAsyncQueriesChunkNum());
        $conditionsId = $conditions->getId();
        foreach ($searchQueriesChunks as $searchQueriesChunk) {
            $queries = [];
            foreach ($searchQueriesChunk as $searchQuery) {
                /** @var SearchQuery $searchQuery */
                $searchQuery->unsetConditions();
                $queries[] = $searchQuery;
            }
            $message = [
                'conditionsId' => $conditionsId,
                'searchQueries' => serialize($queries)
            ];
            $msgBody = json_encode($message);
            $this->producer->publish($msgBody);
        }

        return $conditions->getId();
    }


    /**
     * @param array $data
     * @return SearchConditions
     * @throws SearchConditionException
     */
    private function createSearchConditions(array $data): SearchConditions
    {

        $hash = uniqid('az_', true);
        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $conditions->setSearchHash($hash);
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

    public function getRestrictionsErrors(): ?array
    {
        return $this->restrictionChecker->getErrors();
    }

    /**
     * @return int
     */
    public function getAsyncQueriesChunkNum(): int
    {
        if (null === $this->asyncQueriesChunk) {
            return self::QUERIES_CHUNK_NUM;
        }

        return $this->asyncQueriesChunk;
    }

    /**
     * @param int $asyncQueriesChunk
     */
    public function setAsyncQueriesChunk(int $asyncQueriesChunk): void
    {
        $this->asyncQueriesChunk = $asyncQueriesChunk;
    }


}