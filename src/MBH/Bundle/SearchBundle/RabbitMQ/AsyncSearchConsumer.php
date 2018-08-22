<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\ResultRedisStore;
use MBH\Bundle\SearchBundle\Services\Search\ConsumerSearcher;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    /** @var ConsumerSearcher */
    private $consumerSearch;

    public function __construct(ConsumerSearcher $consumerSearch)
    {
        $this->consumerSearch = $consumerSearch;
    }


    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);
        $conditionsId = $body['conditionsId'];
        $searchQueries = unserialize($body['searchQueries'], [SearchQuery::class => true]);

        $this->consumerSearch->search($conditionsId, $searchQueries);
    }

}