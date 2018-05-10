<?php


namespace MBH\Bundle\BaseBundle\Task;


use MBH\Bundle\BaseBundle\Service\Cache;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class CacheClear implements ConsumerInterface
{

    /** @var Cache */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }


    public function execute(AMQPMessage $msg)
    {
        $clearCollection = unserialize($msg->getBody());
        $this->cache->setClearCollection($clearCollection);
        $this->cache->cleanCache();
    }

}