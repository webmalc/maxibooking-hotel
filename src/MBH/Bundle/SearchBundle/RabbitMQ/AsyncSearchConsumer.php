<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncSearchConsumerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\ResultRedisStore;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    private const ADDITIONAL_RESULTS_LIMIT = 5;
    /** @var Searcher */
    private $searcherFactory;

    /** @var SearchConditionsRepository */
    private $conditionsRepository;
    /**
     * @var ResultRedisStore
     */
    private $resultStore;


    /**
     * AsyncSearchConsumer constructor.
     * @param SearcherFactory $searcherFactory
     * @param SearchConditionsRepository $conditionsRepository
     * @param AsyncResultStoreInterface $resultStore
     */
    public function __construct(SearcherFactory $searcherFactory, SearchConditionsRepository $conditionsRepository, AsyncResultStoreInterface $resultStore)
    {
        $this->searcherFactory = $searcherFactory;
        $this->conditionsRepository = $conditionsRepository;
        $this->resultStore = $resultStore;
    }


    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);
        $conditionsId = $body['conditionsId'];
        $searchQueries = unserialize($body['searchQueries'], [SearchQuery::class => true]);

        /** @var SearchConditions $conditions */
        $conditions = $this->conditionsRepository->find($conditionsId);
        if (!$conditions ) {
            throw new AsyncSearchConsumerException('Error! Can not find SearchConditions for search');
        }
        $hash = $conditions->getSearchHash();
        if ($this->resultStore->getAlreadySearchedDay($hash) > self::ADDITIONAL_RESULTS_LIMIT) {
            $this->resultStore->addFakeReceivedCount($hash);
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


        $this->conditionsRepository->getDocumentManager()->clear();

    }

}