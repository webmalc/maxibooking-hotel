<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\InvalidateQueue;


use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessage;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessageFactory;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class InvalidateQueueCreator implements InvalidateQueueInterface
{
    /** @var ProducerInterface */
    protected $producer;
    /**
     * @var InvalidateMessageFactory
     */
    private $factory;

    /** @var QueryCreatorFactory */
    private $queryCreatorFactory;

    /** @var bool */
    private $isUseCache;

    /**
     * AbstractInvalidateQueueCreator constructor.
     * @param ProducerInterface $producer
     * @param InvalidateMessageFactory $factory
     * @param bool $isUseCache
     */
    public function __construct(ProducerInterface $producer, InvalidateMessageFactory $factory, QueryCreatorFactory $creatorFactory, bool $isUseCache = true)
    {
        $this->producer = $producer;
        $this->factory = $factory;
        $this->isUseCache = $isUseCache;
        $this->queryCreatorFactory = $creatorFactory;
    }

    /**
     * @param object|array $data
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    public function addToQueue($data): void
    {
        $invalidateQuery = $this->createInvalidateQuery($data);
        /** @var InvalidateMessage $message */
        $message = $this->factory->createMessage($invalidateQuery);
        $this->sentToQueue($message);
    }

    /**
     * @param array $data
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    public function addBatchToQueue(array $data): void
    {
        foreach ($data as $value) {
            $this->addToQueue($value);
        }
    }

    /**
     * @param InvalidateMessage $message
     */
    protected function sentToQueue(InvalidateMessage $message): void
    {
        if ($this->isUseCache) {
            $msgBody = serialize($message);
            $this->producer->publish($msgBody);
        }

    }

    /**
     * @param $data
     * @return InvalidateQuery
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    private function createInvalidateQuery($data): InvalidateQuery
    {
        $creator = $this->queryCreatorFactory->create($data);

        return $creator->createInvalidateQuery($data);
    }
}