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

class ResultRedisStore implements AsyncResultStoreInterface
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

    public function store(ResultCacheablesInterface $searchResult): void
    {
        $hash = $searchResult->getSearchHash();
        $uniqueId = $searchResult->getId();
        $key = $hash . $uniqueId;
        /** @var Result $searchResult */
        $this->cache->set($key, $this->serializer->serialize($searchResult));
    }

    /**
     * @param SearchConditions $conditions
     * @param bool $isHideError
     * @param null $grouping
     * @param null|string $serializeType
     * @return mixed
     * @throws AsyncResultReceiverException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function receive(SearchConditions $conditions, bool $isHideError = true, $grouping = null, ?string $serializeType = 'json')
    {
        $results = [];
        $keysForDelete = [];
        $received = 0;
        $hash = $conditions->getSearchHash();

        $expectedResults = $conditions->getExpectedResultsCount();
        $receivedCount = $this->cache->get('received' . $hash);

        if ($expectedResults === (int)$receivedCount) {
            throw new AsyncResultReceiverException('All results were taken.');
        }

        if (null !== $receivedCount && (int)$receivedCount > $expectedResults ) {
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
        $results = array_map([$this->serializer, 'deserialize'], $results);

        $results = $this->finalResultsBuilder
            ->set($results)
            ->hideError($isHideError)
            ->setGrouping($grouping)
            ->serialize($serializeType)
            ->getResults();

        return $results;
    }



}