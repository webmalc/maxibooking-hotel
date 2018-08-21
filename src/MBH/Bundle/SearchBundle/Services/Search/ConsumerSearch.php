<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\ResultRedisStore;

class ConsumerSearch
{

    private const ADDITIONAL_RESULTS_LIMIT = 5;

    /** @var SearchConditionsRepository */
    private $conditionsRepository;

    /** @var ResultRedisStore */
    private $resultStore;
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
        $this->resultStore = $resultStore;
        $this->searcherFactory = $searcherFactory;
    }

    public function search(string $conditionsId, array $searchQueries): void
    {

         /** @var SearchConditions $conditions */
        $conditions = $this->conditionsRepository->find($conditionsId);
        if (!$conditions ) {
            throw new ConsumerSearchException('Error! Can not find SearchConditions for search in consumerSearch');
        }
        $hash = $conditions->getSearchHash();
        if ($this->resultStore->getAlreadySearchedDay($hash) > self::ADDITIONAL_RESULTS_LIMIT) {
            $this->resultStore->addFakeReceivedCount($hash, \count($searchQueries));
        } else {
            $searcher = $this->searcherFactory->getSearcher($conditions->isUseCache());
            $successResults = 0;
            foreach ($searchQueries as $searchQuery) {
                /** @var SearchQuery $searchQuery */
                $searchQuery->setSearchConditions($conditions);
                $result = $searcher->search($searchQuery);
                $this->resultStore->store($result, $conditions);
                /** @var Result $result */
                if ($result->getStatus() === 'ok') {
                    $successResults++;
                }
            }
            if ($successResults > 0) {
                $this->resultStore->increaseAlreadySearchedDay($hash);
            }
        }

        $dm = $this->conditionsRepository->getDocumentManager();
        //** We use a consumer, so clear after each iteration */
        $dm->detach($conditions);
        $dm->flush();
        $dm->clear();

    }
}