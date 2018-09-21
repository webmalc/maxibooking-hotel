<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheWarmerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Services\Search\CacheSearcher;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class WarmUpConsumer implements ConsumerInterface
{

    /** @var CacheSearcher */
    private $cacheSearcher;

    /**
     * WarmUpConsumer constructor.
     * @param CacheSearcher $cacheSearcher
     */
    public function __construct(CacheSearcher $cacheSearcher)
    {
        $this->cacheSearcher = $cacheSearcher;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws CacheWarmerException
     */
    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $searchQueries = unserialize($body, [true]);
        if (is_iterable($searchQueries)) {
            foreach ($searchQueries as $searchQuery) {
                try {
                    $this->cacheSearcher->search($searchQuery);
                } catch (SearchResultComposerException|SharedFetcherException $e) {
                    throw new CacheWarmerException('Error in consumer. '. $e->getMessage());
                }
            }
        }
    }


}