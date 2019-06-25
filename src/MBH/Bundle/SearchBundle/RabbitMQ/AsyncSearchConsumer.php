<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use DateTime;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncSearchConsumer implements ConsumerInterface
{

    /** @var AsyncSearcherInterface[] */
    private $asyncSearcherMap = [];

    /** @var  */
    private $iteration = 0;

    public function addSearcher(string $name, AsyncSearcherInterface $asyncSearcher): void
    {
        $this->asyncSearcherMap[$name] = $asyncSearcher;
    }


    public function execute(AMQPMessage $msg)
    {
        $this->iteration++;
        printf('PID %u. Start %s. Iteration %u.'."\n", getmypid(), (new DateTime())->format('H:i:s'), $this->iteration);
        $body = json_decode($msg->getBody(), true);
        $conditionsId = $body['conditionsId'];
        /** @var QueryGroupInterface $searchQueryGroup */
        $searchQueryGroup = unserialize($body['searchQueriesGroup'], [QueryGroupInterface::class => true]);
        $asyncSearcher = $this->asyncSearcherMap[$searchQueryGroup->getGroupName()];
        $asyncSearcher->search($conditionsId, $searchQueryGroup);
        printf('PID %u. Stop %s. Iteration %u.'."\n", getmypid(), (new DateTime())->format('H:i:s'), $this->iteration);
    }

}