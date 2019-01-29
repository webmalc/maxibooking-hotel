<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearcherFactory
{

    /**
     * @var bool
     * temporary disable cache
     */
    private const IS_USE_CACHE = false;
    /** @var Searcher */
    private $searcher;

    /** @var CacheSearcher */
    private $cacheSearcher;

    /**
     * SearchFactory constructor.
     * @param Searcher $searcher
     * @param CacheSearcher $cacheSearcher
     */
    public function __construct(Searcher $searcher, CacheSearcher $cacheSearcher)
    {
        $this->searcher = $searcher;
        $this->cacheSearcher = $cacheSearcher;
    }


    public function getSearcher(bool $isUseCache = false): SearcherInterface
    {
        if (self::IS_USE_CACHE && $isUseCache) {
            return $this->cacheSearcher;
        }

        return $this->searcher;
    }

}