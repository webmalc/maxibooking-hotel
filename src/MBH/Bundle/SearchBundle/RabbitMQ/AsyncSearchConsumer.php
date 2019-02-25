<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearcher;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    /** @var AsyncSearcherInterface[] */
    private $asyncSearcherMap = [];

    public function addSearcher(string $name, AsyncSearcherInterface $asyncSearcher): void
    {
        $this->asyncSearcherMap[$name] = $asyncSearcher;
    }


    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->getBody(), true);
        $conditionsId = $body['conditionsId'];
        /** @var QueryGroupInterface $searchQueryGroup */
        $searchQueryGroup = unserialize($body['searchQueriesGroup'], [QueryGroupInterface::class => true]);
        $asyncSearcher = $this->asyncSearcherMap[$searchQueryGroup->getGroupName()];
        $asyncSearcher->search($conditionsId, $searchQueryGroup);
    }

}