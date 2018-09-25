<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Services\Cache\SearchCacheInterface;

abstract class AbstractCacheSearcher implements SearcherInterface
{
    /** @var Searcher */
    protected $searcher;
    /**
     * @var SearchCacheInterface
     */
    protected $cache;

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

}