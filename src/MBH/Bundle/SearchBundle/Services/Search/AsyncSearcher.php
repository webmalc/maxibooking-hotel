<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;

class AsyncSearcher
{

    /** @var SearchConditionsRepository */
    private $conditionsRepository;

    /** @var AsyncResultStore */
    private $asyncResultStore;
    /**
     * @var SearcherFactory
     */
    private $searcherFactory;

    /**
     * ConsumerSearch constructor.
     * @param SearchConditionsRepository $conditionsRepository
     * @param AsyncResultStoreInterface $resultStore
     * @param SearcherFactory $searcherFactory
     */
    public function __construct(SearchConditionsRepository $conditionsRepository, AsyncResultStoreInterface $resultStore, SearcherFactory $searcherFactory)
    {
        $this->conditionsRepository = $conditionsRepository;
        $this->asyncResultStore = $resultStore;
        $this->searcherFactory = $searcherFactory;
    }

    public function search(string $conditionsId, QueryGroupInterface $groupedQueries): void
    {
        $searchQueries = $groupedQueries->getSearchQueries();

        /** @var SearchConditions $conditions */
        $conditions = $this->conditionsRepository->find($conditionsId);
        if (!$conditions) {
            throw new ConsumerSearchException('Error! Can not find SearchConditions for search in consumerSearch');
        }
        $hash = $conditions->getSearchHash();
        $searchedDaysCount = $this->asyncResultStore->getAlreadySearchedDay($hash);
        if ($searchedDaysCount > $conditions->getAdditionalResultsLimit()) {
            $this->asyncResultStore->addFakeReceivedCount($hash, \count($searchQueries));
        } else {
            $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());
            $successResults = 0;
            foreach ($searchQueries as $searchQuery) {
                /** @var SearchQuery $searchQuery */
                $searchQuery->setSearchConditions($conditions);
                $result = $searcher->search($searchQuery);
                $this->asyncResultStore->store($result, $conditions);
                if ($result instanceof Result && $result->getStatus() === 'ok') {
                    $successResults++;
                } elseif (\is_array($result) && $result['status'] === 'ok') {
                    $successResults++;
                }
            }
            if ($successResults > 0) {
                $this->asyncResultStore->increaseAlreadySearchedDay($hash);
            }
        }

        $dm = $this->conditionsRepository->getDocumentManager();
        //** We use a consumer, so clear after each iteration */
        $dm->detach($conditions);
        $dm->flush();
        $dm->clear();

    }
}