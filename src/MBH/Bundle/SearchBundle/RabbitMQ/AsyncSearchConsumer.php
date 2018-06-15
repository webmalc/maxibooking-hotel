<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncSearchConsumerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    /** @var Searcher */
    private $searcher;

    /** @var SearchResultHolderRepository */
    private $holderRepository;

    /**
     * AsyncSearchConsumer constructor.
     * @param Searcher $searcher
     * @param SearchResultHolderRepository $holderRepository
     */
    public function __construct(Searcher $searcher, SearchResultHolderRepository $holderRepository)
    {
        $this->searcher = $searcher;
        $this->holderRepository = $holderRepository;
    }


    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);
        $holderId = $body['holderId'];
        $searchQuery = unserialize($body['searchQuery'], [SearchQuery::class => true]);

        /** @var SearchResultHolder $holder */
        $holder = $this->holderRepository->find($holderId);
        if (!$holder || ! $conditions = $holder->getSearchConditions()) {
            throw new AsyncSearchConsumerException('Error! Can not find holder for search');
        }
        /** @var SearchQuery $searchQuery */
        $searchQuery->setSearchConditions($conditions);
        try {
            $result = $this->searcher->search($searchQuery);
        } catch (SearchException $exception) {
            $result = SearchResult::createErrorResult($exception);
        }

        $holder->addSearchResult($result);
        $this->holderRepository->getDocumentManager()->flush();
    }

}