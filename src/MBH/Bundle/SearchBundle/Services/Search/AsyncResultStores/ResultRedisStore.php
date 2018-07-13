<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
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
    public function __construct(AbstractCache $cache)
    {
        $this->cache = $cache;
    }

    public function store(Result $searchResult): void
    {
        $key = $searchResult->getResultConditions()->getSearchHash();
        $this->cache->set($key, $searchResult);
    }

    public function receive(SearchConditions $conditions): array
    {
        $results = [];
        $hash = $conditions->getSearchHash();
        return $results;
    }

}