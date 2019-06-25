<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use DateTime;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherInterface;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\BatchConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Stopwatch\Stopwatch;

class AsyncSearchBatchConsumer implements BatchConsumerInterface
{

    /** @var AsyncSearcherInterface */
    private $asyncSearcher;

    /** @var int */
    private $iteration = 0;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * AsyncSearchBatchConsumer constructor.
     * @param AsyncSearcherInterface $asyncSearcher
     */
    public function __construct(AsyncSearcherInterface $asyncSearcher, Logger $logger)
    {
        $this->asyncSearcher = $asyncSearcher;
    }

    public function batchExecute(array $messages)
    {
        $this->iteration++;
        printf('PID %u. Start %s. Iteration %u'."\n", getmypid(), (new DateTime())->format('H:i:s'), $this->iteration);

        foreach ($messages as $message) {
            /** @var AMQPMessage $message */
            $body = json_decode($message->getBody(), true);
            $conditionsId = $body['conditionsId'];
            /** @var QueryGroupInterface $searchQueryGroup */
            $searchQueryGroup = unserialize($body['searchQueriesGroup'], [QueryGroupInterface::class => true]);
//            printf('PID %u. Queries %u', getmypid(), count($searchQueryGroup->getSearchQueries()));
            $this->asyncSearcher->search($conditionsId, $searchQueryGroup);
        }

        printf('PID %u. Stop %s. Iteration %u'."\n", getmypid(), (new DateTime())->format('H:i:s'), $this->iteration);

        return true;

    }

}