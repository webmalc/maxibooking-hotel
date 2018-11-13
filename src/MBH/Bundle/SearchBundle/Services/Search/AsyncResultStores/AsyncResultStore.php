<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultCacheablesInterface;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use MBH\Bundle\SearchBundle\Services\FinalSearchResultsBuilder;
use Predis\Client;
use Symfony\Component\Cache\Simple\RedisCache;

class AsyncResultStore implements AsyncResultStoreInterface
{

    /** @var Client */
    private $cache;
    /**
     * @var ResultSerializer
     */
    private $serializer;
    /**
     * @var FinalSearchResultsBuilder
     */
    private $finalResultsBuilder;


    /**
     * ResultRedisStore constructor.
     * @param Client $cache
     * @param ResultSerializer $serializer
     * @param FinalSearchResultsBuilder $resultsBuilder
     */
    public function __construct(Client $cache, ResultSerializer $serializer, FinalSearchResultsBuilder $resultsBuilder)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->finalResultsBuilder = $resultsBuilder;
    }

    public function store($result,  SearchConditions $conditions): void
    {
        $hash = $conditions->getSearchHash();
        if ($result instanceof Result) {
            $uniqueId = $result->getId();
            $data = $this->serializer->serialize($result);
        } else {
            $uniqueId = $result['id'];
            $data = $this->serializer->encodeArrayToJson($result);
        }

        $key = $hash . $uniqueId;
        /** @var Result $searchResult */
        $this->cache->set($key, $data);
    }

    /**
     * @param SearchConditions $conditions
     * @param int $errorLevel
     * @param null $grouping
     * @param bool $isCreateJson
     * @param bool $isCreateAnswer
     * @return mixed
     * @throws AsyncResultReceiverException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function receive(SearchConditions $conditions, int $errorLevel = 0, $grouping = null, bool $isCreateJson = false, bool $isCreateAnswer = false)
    {
        $results = [];
        $keysForDelete = [];
        $received = 0;
        $hash = $conditions->getSearchHash();

        $expectedResults = $conditions->getExpectedResultsCount();
        $receivedCount = (int)$this->cache->get('received' . $hash) + (int)$this->cache->get('received_fake'.$hash);

        if ($expectedResults === $receivedCount) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if (null !== $receivedCount && $receivedCount > $expectedResults ) {
            throw new AsyncResultReceiverException('Some error! Taken results more than Expected!');
        }

        $prefix = (string)$this->cache->getOptions()->prefix;
        $keys = $this->cache->keys($hash . '*');
        array_walk($keys, function (&$key) use ($prefix) {
            $key = str_replace($prefix, '', $key);
        });

        foreach ($keys as $key) {
            $results[] = $this->cache->get($key);
            $keysForDelete[] = $key;
        }
        if (\count($keysForDelete)) {
            $received = $this->cache->del($keysForDelete);
        }

        $this->cache->set('received'. $hash, (int)$receivedCount + $received);
        $results = array_map([$this->serializer, 'decodeJsonToArray'], $results);

        $results = $this->finalResultsBuilder
            ->set($results)
            ->errorFilter($errorLevel)
            ->setGrouping($grouping)
            ->createJson($isCreateJson)
            ->createAnswer($isCreateAnswer)
            ->getResults();

        return $results;
    }

    /**
     * @param string $hash
     * @param int $number
     */
    public function addFakeReceivedCount(string $hash, int $number): void
    {
        $fakeReceived = $this->cache->get('received_fake' . $hash);
        $this->cache->set('received_fake' . $hash, (int)$fakeReceived + $number);
    }

    public function increaseAlreadySearchedDay(string $hash): void
    {
        $alreadySearched = $this->getAlreadySearchedDay($hash);
        $this->cache->set('already_received_group_' . $hash, $alreadySearched + 1);
    }

    /**
     * @param string $hash
     * @return int
     */
    public function getAlreadySearchedDay(string $hash): int
    {
        return (int)$this->cache->get('already_received_group_' . $hash);
    }

}