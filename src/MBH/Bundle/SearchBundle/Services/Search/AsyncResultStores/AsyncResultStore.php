<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


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

    public function store($result, SearchConditions $conditions): void
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
    public function receive(
        SearchConditions $conditions,
        $grouperName = null,
        bool $isCreateJson = false,
        bool $isCreateAnswer = false
    ) {
        $results = [];
        $keysForDelete = [];
        $received = 0;
        $hash = $conditions->getSearchHash();

        $expectedResults = $conditions->getExpectedResultsCount();
        $receivedCount = (int)$this->cache->get('received'.$hash) + (int)$this->cache->get('received_fake'.$hash);

        if ($expectedResults === $receivedCount) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if (null !== $receivedCount && $receivedCount > $expectedResults) {
            throw new AsyncResultReceiverException('Some error! Taken results more than Expected!');
        }

        $keys = $this->cache->smembers($hash);

        foreach ($keys as $key) {
            $results[] = $this->cache->get($key);
            $keysForDelete[] = $key;
            $this->cache->srem($hash, $key);
        }
        if (\count($keysForDelete)) {
            $received = $this->cache->del($keysForDelete);

        }

        $this->cache->set('received'.$hash, $receivedCount + $received);
        $results = array_map([$this->serializer, 'decodeJsonToArray'], $results);

        $results = $this->finalResultsBuilder->createAnswer(
            $results,
            $conditions->getErrorLevel(),
            $isCreateJson,
            $isCreateAnswer,
            $grouperName
        );

        return $results;
    }

    /**
     * @param string $hash
     * @param int $number
     */
    public function addFakeReceivedCount(string $hash, int $number): void
    {
        $fakeReceived = $this->cache->get('received_fake'.$hash);
        $this->cache->set('received_fake'.$hash, (int)$fakeReceived + $number, 'EX', self::EXPIRE_TIME);
    }

}