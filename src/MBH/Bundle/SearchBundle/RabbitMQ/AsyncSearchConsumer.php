<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Document\SearchResult;
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

    /** @var SearchConditionsRepository */
    private $conditionsRepository;

    /**
     * AsyncSearchConsumer constructor.
     * @param Searcher $searcher
     * @param SearchConditionsRepository $conditionsRepository
     */
    public function __construct(Searcher $searcher, SearchConditionsRepository $conditionsRepository)
    {
        $this->searcher = $searcher;
        $this->conditionsRepository = $conditionsRepository;
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
        $dm = $this->conditionsRepository->getDocumentManager();
        foreach ($searchQueries as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $searchQuery->setSearchConditions($conditions);
            try {
                $result = $this->searcher->search($searchQuery);
            } catch (SearchException $exception) {
                $result = SearchResult::createErrorResult($exception);
            }

            $dm->persist($result);
            $dm->flush($result);
        }

    }

}