<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\RestrictionsCheckerService;

class Search
{
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

    public function __construct(RestrictionsCheckerService $restrictionsChecker, Searcher $searcher, DocumentManager $documentManager, ClientConfigRepository  $configRepository)
    {
        $this->restrictionChecker = $restrictionsChecker;
        $this->searcher = $searcher;
        $this->dm = $documentManager;
        $this->isSaveQueryStat = $configRepository->fetchConfig()->isQueryStat();
    }


    public function search(array $searchQueries, SearchConditions $conditions, bool $isAsync = false): array
    {
        /** @var SearchQuery $searchQuery */
        if ($this->isSaveQueryStat) {
            $this->saveQueryStat($conditions);
        }

        $results = [];

        if (self::PRE_RESTRICTION_CHECK) {
            $this->restrictionChecker->setConditions($conditions);
            $searchQueries = array_filter($searchQueries, [$this->restrictionChecker, 'check']);
        }

        $this->searchHash = uniqid(gethostname(), true);
        $this->searchQueriesCount = \count($searchQueries);

        if (!$isAsync) {
            foreach ($searchQueries as $searchQuery) {
                try {
                    $results[$this->searchHash][] = $this->searcher->search($searchQuery);
                } catch (SearchException $e) {
                    $results[$this->searchHash][] = $e->getMessage();
                }
            }
        } else {
            //** TODO: Тут должен жить продюсер */
            null;
        }

        return $results;
    }

    private function saveQueryStat(SearchConditions $conditions): void
    {
        $this->dm->persist($conditions);
        $this->dm->flush($conditions);
    }

    public function getSearchHash(): string
    {
        return $this->searchHash;
    }

    public function getSearchCount(): int
    {
        return $this->searchQueriesCount;
    }

}