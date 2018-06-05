<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;

class Search
{
    /** @var bool  */
    public const PRE_RESTRICTION_CHECK = true;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var Searcher */
    private $searcher;

    /** @var string */
    private $searchHash;

    /** @var int */
    private $searchQueriesCount;

    /** @var DocumentManager */
    private $dm;

    /** @var bool */
    private $isSaveQueryStat;

    /** @var SearchConditionsCreator */
    private $conditionsCreator;

    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param Searcher $searcher
     * @param DocumentManager $documentManager
     * @param ClientConfigRepository $configRepository
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchQueryGenerator $queryGenerator
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        Searcher $searcher,
        DocumentManager $documentManager,
        ClientConfigRepository $configRepository,
        SearchConditionsCreator $conditionsCreator,
        SearchQueryGenerator $queryGenerator)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcher = $searcher;
        $this->dm = $documentManager;
        $this->isSaveQueryStat = $configRepository->fetchConfig()->isQueryStat();
        $this->conditionsCreator = $conditionsCreator;
        $this->queryGenerator = $queryGenerator;

    }


    /**
     * @param array $data
     * @param bool $isAsync
     * @return array|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    public function search(array $data, bool $isAsync = false): ?array
    {
        if (!$isAsync) {
            return $this->searchSync($data);
        }

        return $this->searchAsync($data);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerServiceException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    public function searchSync(array $data): array
    {
        $searchQueries = $this->prepareQueries($data);
        $results = [];
        foreach ($searchQueries as $searchQuery) {
            try {
                $results[] = [
                    'status' => 'ok',
                    'result' => $this->searcher->search($searchQuery)
                ];
            } catch (SearchException $e) {
                $results[] = [
                    'status' => 'error',
                    'result' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * @param array $data
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    public function searchAsync(array $data): array
    {
        $searchQueries = $this->prepareQueries($data);
        //** TODO: Create Queue */
        return ['queue' => 'ok'];
    }

    /**
     * @param array $data
     * @return array|SearchQuery[]
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    private function prepareQueries(array $data): array
    {
        $this->searchHash = uniqid(gethostname(), true);
        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $conditions->setSearchHash($this->searchHash);

        $searchQueries = $this->queryGenerator->generateSearchQueries($conditions);



        if ($this->isSaveQueryStat) {
            $this->saveQueryStat($conditions);
        }

        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }
        $this->searchQueriesCount = \count($searchQueries);


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

    /**
     * @return string
     */
    public function getSearchHash(): string
    {
        return $this->searchHash;
    }

    /**
     * @return int
     */
    public function getSearchCount(): int
    {
        return $this->searchQueriesCount;
    }

}