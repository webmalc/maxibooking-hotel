<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class Search
{
    /** @var int */
    public const QUERIES_CHUNK_NUM = 20;

    /** @var bool */
    public const PRE_RESTRICTION_CHECK = false;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var SearcherInterface */
    private $searcher;

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

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param SearcherInterface $searcher
     * @param DocumentManager $documentManager
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchQueryGenerator $queryGenerator
     * @param ProducerInterface $producer
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        SearcherInterface $searcher,
        DocumentManager $documentManager,
        SearchConditionsCreator $conditionsCreator,
        SearchQueryGenerator $queryGenerator,
        ProducerInterface $producer
    )
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcher = $searcher;
        $this->dm = $documentManager;
        $this->conditionsCreator = $conditionsCreator;
        $this->queryGenerator = $queryGenerator;
        $this->producer = $producer;
    }


    /**
     * @param array $data
     * @return Result[]
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function searchSync(array $data): array
    {
        $conditions = $this->createSearchConditions($data);
        $searchQueries = $this->createSearchQueries($conditions);
        $results = [];
        foreach ($searchQueries as $searchQuery) {
            $results[] = $this->searcher->search($searchQuery);
        }

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
        $searchQueries = $this->createSearchQueries($conditions);
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

        $hash = uniqid(\AppKernel::DEFAULT_CLIENT, true);
        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $conditions->setSearchHash($hash);
        $this->saveQueryStat($conditions);

        return $conditions;
    }

    /**
     * @param SearchConditions $conditions
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function createSearchQueries(SearchConditions $conditions): array
    {

        $searchQueries = $this->queryGenerator->generateSearchQueries($conditions);

        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }
        $conditions->setExpectedResultsCount(\count($searchQueries));

        return $searchQueries;
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