<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultCacheablesInterface;
use Predis\Client;
use Symfony\Component\Cache\Simple\AbstractCache;
use Symfony\Component\Cache\Simple\RedisCache;

class ResultRedisStore implements AsyncResultStoreInterface
{

    /** @var RedisCache */
    private $cache;

    /**
     * ResultRedisStore constructor.
     * @param AbstractCache $cache
     */
    public function __construct(Client $cache)
    {
        $this->cache = $cache;
    }

    public function store(ResultCacheablesInterface $searchResult): void
    {
        $hash = $searchResult->getSearchHash();
        $uniqueId = $searchResult->getId();
        $key = $hash . $uniqueId;
        $this->cache->set($key, serialize($searchResult));
    }

    /**
     * @param SearchConditions $conditions
     * @return Result[]
     * @throws AsyncResultReceiverException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function receive(SearchConditions $conditions): array
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

        $results = array_map('\unserialize', $results);

        return $results;
    }



}