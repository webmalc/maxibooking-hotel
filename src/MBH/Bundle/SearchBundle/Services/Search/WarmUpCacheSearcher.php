<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class WarmUpCacheSearcher extends AbstractCacheSearcher
{
    /**
     * @param SearchQuery $searchQuery
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function search(SearchQuery $searchQuery): void
    {
        $result = $this->searcher->search($searchQuery);
        $this->cache->saveToCache($result, $searchQuery);
    }

}