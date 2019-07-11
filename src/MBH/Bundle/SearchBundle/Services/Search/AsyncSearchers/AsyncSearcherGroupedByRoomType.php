<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use function is_array;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\QueryGroups\SearchNecessarilyInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;

class AsyncSearcherGroupedByRoomType implements AsyncSearcherInterface
{

    /** @var SearchConditionsRepository */
    private $conditionsRepository;

    /** @var AsyncResultStore */
    private $asyncResultStore;
    /**
     * @var SearcherFactory
     */
    private $searcherFactory;

    /** @var AsyncSearchDecisionMakerInterface */
    private $decisionMaker;
    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * ConsumerSearch constructor.
     * @param SearchConditionsRepository $conditionsRepository
     * @param AsyncResultStoreInterface $resultStore
     * @param SearcherFactory $searcherFactory
     * @param AsyncSearchDecisionMakerInterface $decisionMaker
     * @param DataManager $dataManager
     */
    public function __construct(
        SearchConditionsRepository $conditionsRepository,
        AsyncResultStoreInterface $resultStore,
        SearcherFactory $searcherFactory,
        AsyncSearchDecisionMakerInterface $decisionMaker,
        DataManager $dataManager
    ) {
        $this->conditionsRepository = $conditionsRepository;
        $this->asyncResultStore = $resultStore;
        $this->searcherFactory = $searcherFactory;
        $this->decisionMaker = $decisionMaker;
        $this->dataManager = $dataManager;
    }


    /**
     * @param string $conditionsId
     * @param QueryGroupInterface $searchQueryGroup
     * @throws ConsumerSearchException
     * @throws LockException
     * @throws MappingException
     * @throws CommunicationException
     * @throws ServerException
     * @throws AbortedMultiExecException
     */
    public function search(string $conditionsId, QueryGroupInterface $searchQueryGroup): void
    {
        if (!$searchQueryGroup instanceof QueryGroupByRoomType) {
            throw new ConsumerSearchException('Wrong searchGroup in AsyncSearcher.');
        }

        /** @var SearchConditions $conditions */
        $conditions = $this->conditionsRepository->find($conditionsId);
        if (!$conditions) {
            throw new ConsumerSearchException('Error! Can not find SearchConditions for search in consumerSearch');
        }

        if ($this->decisionMaker->isNeedSearch($conditions, $searchQueryGroup)) {
            $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());
            $searchQueries = $searchQueryGroup->getSearchQueries();
            $results = [];
            foreach ($searchQueries as $searchQuery) {
                /** @var SearchQuery $searchQuery */
                $searchQuery->setSearchConditions($conditions);
                $results[] = $searcher->search($searchQuery);
            }
            $this->storeResults($results, $conditions, $searchQueryGroup);

        } else {
            $this->asyncResultStore->addFakeToStock(
                $conditions->getSearchHash(),
                $searchQueryGroup->countQueries()
            );
        }

        $dm = $this->conditionsRepository->getDocumentManager();
        //** We use a consumer, so clear after each iteration */
        $dm->detach($conditions);
        $dm->flush();
        $dm->clear();
        $this->clearTemporaryData();

    }

    private function clearTemporaryData(): void
    {
        $this->dataManager->cleanMemoryData();
    }

    private function storeResults(array $results, SearchConditions $conditions, QueryGroupInterface $queryGroup)
    {
        $founded = false;
        foreach ($results as $result) {
            if (($result instanceof Result && $result->getStatus() === 'ok')
                || (is_array($result) && $result['status'] === 'ok')) {
                $founded = true;
            }

        }
        if ($founded) {
            $this->decisionMaker->markFoundedResults($conditions, $queryGroup);
        }
        if ($this->decisionMaker->canIStoreInStock($conditions, $queryGroup)) {
            foreach ($results as $result) {
                $this->asyncResultStore->storeInStock($result, $conditions);
            }
        } else {
            $this->asyncResultStore->addFakeToStock($conditions->getSearchHash(), count($results));
        }

    }

}