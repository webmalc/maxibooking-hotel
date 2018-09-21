<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class WarmUpSearcher
{

    /** @var ProducerInterface */
    private $producer;

    /**
     * WarmUpSearcher constructor.
     * @param ProducerInterface $producer
     */
    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }


    /**
     * @param SearchQuery[] $searchQueries
     */
    public function search(array $searchQueries): void
    {
        $message = serialize($searchQueries);
        $this->producer->publish($message);
    }

}