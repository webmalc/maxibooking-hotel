<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherInterface;
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