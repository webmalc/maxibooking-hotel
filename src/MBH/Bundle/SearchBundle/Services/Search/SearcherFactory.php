<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearcherFactory
{
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
        if ($isUseCache) {
            return $this->cacheSearcher;
        }

        return $this->searcher;
    }

}