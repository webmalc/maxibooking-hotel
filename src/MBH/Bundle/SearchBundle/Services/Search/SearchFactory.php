<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchFactory implements SearcherInterface
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


    public function search(SearchQuery $searchQuery): Result
    {
        if ($searchQuery->isUseCache()) {
            return $this->cacheSearcher->search($searchQuery);
        }

        return $this->searcher->search($searchQuery);
    }

}