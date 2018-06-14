<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Lib\ErrorSearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchQueryGenerator;
use Symfony\Component\Serializer\Serializer;

class Search
{
    /** @var bool  */
    public const PRE_RESTRICTION_CHECK = true;

    /** @var RestrictionsCheckerService */
    private $restrictionChecker;

    /** @var Searcher */
    private $searcher;

    /** @var DocumentManager */
    private $dm;

    /** @var SearchConditionsCreator */
    private $conditionsCreator;

    /** @var SearchQueryGenerator */
    private $queryGenerator;

    /** @var Serializer */
    private $serializer;

    /**
     * Search constructor.
     * @param RestrictionsCheckerService $restrictionsChecker
     * @param Searcher $searcher
     * @param DocumentManager $documentManager
     * @param SearchConditionsCreator $conditionsCreator
     * @param SearchQueryGenerator $queryGenerator
     * @param Serializer $serializer
     */
    public function __construct(
        RestrictionsCheckerService $restrictionsChecker,
        Searcher $searcher,
        DocumentManager $documentManager,
        SearchConditionsCreator $conditionsCreator,
        SearchQueryGenerator $queryGenerator,
        Serializer $serializer
    )
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcher = $searcher;
        $this->dm = $documentManager;
        $this->conditionsCreator = $conditionsCreator;
        $this->queryGenerator = $queryGenerator;
        $this->serializer = $serializer;
    }


    /**
     * @param array $data
     * @return array
     * @throws MongoDBException
     * @throws DataHolderException
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function searchSync(array $data): array
    {
        $conditions = $this->createSearchConditions($data);
        $searchQueries = $this->createSearchQueries($conditions);
        $results = [];
        foreach ($searchQueries as $searchQuery) {
            try {
                $results[] = $this->searcher->search($searchQuery);
            } catch (SearchException $e) {
                $results[] = ErrorSearchResult::createErrorResult($e);
            }
        }

        return $results;
    }


    public function searchAsync(array $data): string
    {
        $conditions = $this->createSearchConditions($data);
        $searchQueries = $this->createSearchQueries($conditions);
        $holder = new SearchResultHolder();
        $holder
            ->setExpectedResultsCount(\count($searchQueries))
            ->setSearchConditions($conditions)
        ;

        $this->dm->persist($holder);
        $this->dm->flush($holder);

        foreach ($searchQueries as $searchQuery) {
            $searchQuery->unsetConditions();
            $serialized[] = $this->serializer->serialize($searchQuery, 'json');
        }

        return $holder->getId();
    }


    private function createSearchConditions(array $data): SearchConditions
    {
        $hash = uniqid(gethostname(), true);
        $conditions = $this->conditionsCreator->createSearchConditions($data);
        $conditions->setSearchHash($hash);
        $this->saveQueryStat($conditions);

        return $conditions;
    }

    private function createSearchQueries(SearchConditions $conditions): array
    {

        $searchQueries = $this->queryGenerator->generateSearchQueries($conditions);


        if (self::PRE_RESTRICTION_CHECK) {
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }

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

}