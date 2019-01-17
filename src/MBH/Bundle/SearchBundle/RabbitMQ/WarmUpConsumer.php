<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheWarmerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\SearcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\WarmUpCacheSearcher;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class WarmUpConsumer implements ConsumerInterface
{

    /** @var WarmUpCacheSearcher */
    private $cacheSearcher;

    /** @var SearchConditionsRepository */
    private $searchConditionRepository;

    /**
     * WarmUpConsumer constructor.
     * @param SearcherInterface $cacheSearcher
     * @param SearchConditionsRepository $conditionsRepository
     */
    public function __construct(SearcherInterface $cacheSearcher, SearchConditionsRepository $conditionsRepository)
    {
        $this->cacheSearcher = $cacheSearcher;
        $this->searchConditionRepository = $conditionsRepository;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws CacheWarmerException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearcherException
     */
    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        $searchQueries = unserialize($body, [true]);
        if (is_iterable($searchQueries)) {
            foreach ($searchQueries as $searchQuery) {
                try {
                    $this->searchConditionsRecovery($searchQuery);
                    $this->cacheSearcher->search($searchQuery);
                } catch (SearchResultComposerException|SharedFetcherException $e) {
                    throw new CacheWarmerException('Error in consumer. '. $e->getMessage());
                }
            }
        }
    }

    /**
     * @param SearchQuery $query
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    private function searchConditionsRecovery(SearchQuery $query): void
    {
        if (null === $query->getSearchConditions()) {
            /** @var SearchConditions $conditions */
            $conditions = $this->searchConditionRepository->find($query->getSearchConditionsId());
            $query->setSearchConditions($conditions);
        }
    }


}