<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class WarmUpCacheSearchResult extends AbstractCacheSearchResult
{
    /**
     * @param SearchQuery $searchQuery
     * @param bool $hydrated
     * @return bool
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function searchInCache(SearchQuery $searchQuery, $hydrated = true): bool
    {
        $key = $this->createKey($searchQuery);

        return $this->redis->exists($key);
    }

    protected function createKey(SearchQuery $searchQuery): string
    {
        return $this->keyCreator->createWarmUpKey($searchQuery);
    }


}