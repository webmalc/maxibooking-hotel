<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherInterface;
use OldSound\RabbitMqBundle\RabbitMq\BatchConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchBatchConsumer implements BatchConsumerInterface
{

    /** @var AsyncSearcherInterface */
    private $asyncSearcher;

    /**
     * AsyncSearchBatchConsumer constructor.
     * @param AsyncSearcherInterface $asyncSearcher
     */
    public function __construct(AsyncSearcherInterface $asyncSearcher)
    {
        $this->asyncSearcher = $asyncSearcher;
    }

    public function batchExecute(array $messages)
    {
        foreach ($messages as $message) {
            /** @var AMQPMessage $message */
            $body = json_decode($message->getBody(), true);
            $conditionsId = $body['conditionsId'];
            /** @var QueryGroupInterface $searchQueryGroup */
            $searchQueryGroup = unserialize($body['searchQueriesGroup'], [QueryGroupInterface::class => true]);
            $this->asyncSearcher->search($conditionsId, $searchQueryGroup);
        }

        return true;
    }

}