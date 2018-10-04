<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage;
use MBH\Bundle\SearchBundle\Services\Cache\Invalidate\SearchCacheInvalidator;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class InvalidateConsumer implements ConsumerInterface
{
    /** @var SearchCacheInvalidator */
    private $invalidator;


    /**
     * InvalidateConsumer constructor.
     * @param SearchCacheInvalidator $invalidator
     */
    public function __construct(SearchCacheInvalidator $invalidator)
    {
        $this->invalidator = $invalidator;
    }


    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->getBody(), [InvalidateMessage::class => true]);
        $this->invalidator->invalidate($message);
    }

}