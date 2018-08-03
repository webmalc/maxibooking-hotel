<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
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

    public function search(SearchQuery $searchQuery): Result
    {
        if ($searchQuery->isUseCache()) {
            $result = $this->cache->searchInCache($searchQuery);
            if (null !== $result){
                return $result;
            }
        }

        try {
            $result = $this->searcher->search($searchQuery);
        } catch (SearchException $e) {
            $result = Result::createErrorResult($searchQuery, $e);
        }

        if ($searchQuery->isUseCache()) {
            $this->cache->saveToCache($result);
        }


        return $result;
    }


}