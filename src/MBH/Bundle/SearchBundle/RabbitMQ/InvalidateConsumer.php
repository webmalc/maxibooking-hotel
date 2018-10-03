<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateAdapterInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage\InvalidateMessage;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage\InvalidateMessageInterface;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
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
        $body = json_decode($msg->getBody(), true);
        /** @var  InvalidateAdapterInterface $message */
        $message = unserialize($body, [InvalidateAdapterInterface::class => true]);
        $this->invalidator->invalidate($message);
    }

}