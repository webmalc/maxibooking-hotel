<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use function count;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use MBH\Bundle\SearchBundle\Services\Search\FinalSearchResultsAnswerManager;
use Predis\Client;

class AsyncResultStore implements AsyncResultStoreInterface
{
    public const EXPIRE_TIME = 300;

    /** @var Client */
    private $cache;
    /**
     * @var ResultSerializer
     */
    private $serializer;
    /**
     * @var FinalSearchResultsAnswerManager
     */
    private $finalResultsBuilder;

    /**
     * ResultRedisStore constructor.
     * @param Client $cache
     * @param ResultSerializer $serializer
     * @param FinalSearchResultsAnswerManager $resultsBuilder
     */
    public function __construct(
        Client $cache,
        ResultSerializer $serializer,
        FinalSearchResultsAnswerManager $resultsBuilder
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->finalResultsBuilder = $resultsBuilder;
    }

    public function storeInStock($result, SearchConditionsInterface $conditions): void
    {
        $hash = $conditions->getSearchHash();
        if ($result instanceof Result) {
            $resultUniqueId = $result->getId();
            $data = $this->serializer->serialize($result);
        } else {
            $resultUniqueId = $result['id'];
            $data = $this->serializer->encodeArrayToJson($result);
        }

        /** @var Result $searchResult */
        $this->cache->set($resultUniqueId, $data, 'EX', self::EXPIRE_TIME);
        $this->cache->sadd($hash, [$resultUniqueId]);
    }

    /**
     * @param SearchConditions $conditions
     * @param null $grouperName
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @return mixed
     * @throws AsyncResultReceiverException
     * @throws GroupingFactoryException
     */
    public function receiveFromStock(
        SearchConditionsInterface $conditions,
        $grouperName = null,
        bool $isCreateJson = false,
        bool $isCreateAnswer = false
    ) {
        $results = [];
        $keysForDelete = [];
        $received = 0;
        $hash = $conditions->getSearchHash();

        $expectedResults = $conditions->getExpectedResultsCount();
        $redisReceived = (int)$this->cache->get($this->createReceivedKey($hash));
        $redisReceivedFake = (int)$this->cache->get($this->createFakeKey($hash));
        $receivedCount = $redisReceived + $redisReceivedFake;

        if ($expectedResults === $receivedCount) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if ((null !== $receivedCount) && ($receivedCount > $expectedResults)) {
            throw new AsyncResultReceiverException('Some error! Taken results more than Expected!');
        }

        $keys = $this->cache->smembers($hash);

        foreach ($keys as $key) {
            $results[] = $this->cache->get($key);
            $keysForDelete[] = $key;
            $this->cache->srem($hash, $key);
        }
        if (count($keysForDelete)) {
            $received = $this->cache->del($keysForDelete);

        }

        $this->cache->transaction()->incrby($this->createReceivedKey($hash), $received)->exec();

        $results = array_map([$this->serializer, 'decodeJsonToArray'], $results);

        //** вынести в отдельный сервис для формирования результатов ? */
        $results = $this->finalResultsBuilder->createAnswer(
            $results,
            $conditions->getErrorLevel(),
            $isCreateJson,
            $isCreateAnswer,
            $grouperName,
            $conditions
        );

        return $results;
    }

    /**
     * @param string $hash
     * @param int $amount
     */
    public function addFakeToStock(string $hash, int $amount): void
    {
        $key = $this->createFakeKey($hash);
        $this->cache->transaction()->incrby($key, $amount)->expire($key, static::EXPIRE_TIME)->exec();

    }

    private function createReceivedKey($hash): string
    {
        return 'received'.$hash;
    }

    private function createFakeKey(string $hash): string
    {
        return 'received_fake'.$hash;
    }

}