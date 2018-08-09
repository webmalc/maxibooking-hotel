<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\SearchCacheInterface;

class CacheSearcher implements SearcherInterface
{
    /** @var Searcher */
    private $searcher;
    /**
     * @var SearchCacheInterface
     */
    private $cache;

    /**
     * CacheSearcher constructor.
     * @param Searcher $searcher
     * @param SearchCacheInterface $cache
     */
    public function __construct(Searcher $searcher, SearchCacheInterface $cache)
    {
        $this->searcher = $searcher;
        $this->cache = $cache;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return Result
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): Result
    {
        $result = $this->cache->searchInCache($searchQuery);
        if (null !== $result) {
            return $result;
        }
        $result = $this->searcher->search($searchQuery);
        $this->cache->saveToCache($result, $searchQuery);

        return $result;
    }


}