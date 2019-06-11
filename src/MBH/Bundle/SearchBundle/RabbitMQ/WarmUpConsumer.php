<?php


namespace MBH\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheWarmerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\GarbageCollector\GarbageCollector;
use MBH\Bundle\SearchBundle\Services\Search\SearcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\WarmUpCacheSearcher;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class WarmUpConsumer implements ConsumerInterface
{

    /** @var WarmUpCacheSearcher */
    private $warmUpCacheSearcher;

    /** @var SearchConditionsRepository */
    private $searchConditionRepository;

    /** @var array */
    private $conditions = [];

    /** @var array */
    private $garbageHashes = [];
    /**
     * @var GarbageCollector
     */
    private $collector;

    /**
     * WarmUpConsumer constructor.
     * @param SearcherInterface $cacheSearcher
     * @param SearchConditionsRepository $conditionsRepository
     */
    public function __construct(SearcherInterface $cacheSearcher, SearchConditionsRepository $conditionsRepository/*, GarbageCollector $collector*/)
    {
        $this->warmUpCacheSearcher = $cacheSearcher;
        $this->searchConditionRepository = $conditionsRepository;
//        $this->collector = $collector;
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
                    /** @var SearchQuery $searchQuery */
                    $this->searchConditionsRecovery($searchQuery);
                    $this->warmUpCacheSearcher->search($searchQuery);
                } catch (SearchResultComposerException|SharedFetcherException $e) {
                    throw new CacheWarmerException('Error in consumer. '. $e->getMessage());
                } finally {
                    $this->garbageHashes[] = $searchQuery->getSearchHash();
                }
            }
        }
        $this->collectGarbage();
    }

    /**
     * @param SearchQuery $query
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws CacheWarmerException
     */
    private function searchConditionsRecovery(SearchQuery $query): void
    {
        if (null === $query->getSearchConditions()) {
            $conditionsId = $query->getSearchConditionsId();
            if (null === $conditions = $this->conditions[$conditionsId] ?? null) {
                $conditions = $this->searchConditionRepository->find($conditionsId);
                $this->conditions[$conditionsId] = $conditions;
            }
            /** @var SearchConditions $conditions */
            if (null === $conditions) {
                throw new CacheWarmerException('No Conditions in consumer exists');
            }
            $query->setSearchConditions($conditions);
        }
    }

    private function collectGarbage(): void
    {
        $hashes = array_unique($this->garbageHashes);
        $this->collector->collect($hashes);
        $this->conditions = [];
        $this->garbageHashes = [];
    }


}