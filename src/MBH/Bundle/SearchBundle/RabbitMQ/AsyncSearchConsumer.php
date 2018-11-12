<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearcher;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    /** @var AsyncSearcher */
    private $consumerSearch;

    public function __construct(AsyncSearcher $consumerSearch)
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