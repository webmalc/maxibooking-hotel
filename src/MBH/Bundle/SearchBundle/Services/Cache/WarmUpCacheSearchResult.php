<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheWarmerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class WarmUpCacheSearchResult extends AbstractCacheSearchResult
{
    /**
     * @param SearchQuery $searchQuery
     * @param bool $hydrated
     * @throws CacheWarmerException
     */
    public function searchInCache(SearchQuery $searchQuery, $hydrated = true): void
    {
        throw new CacheWarmerException('WarmUp has not to search in cache!');
    }

    /**
     * @param SearchQuery $searchQuery
     * @return string
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    protected function createKey(SearchQuery $searchQuery): string
    {
        return $this->keyCreator->createWarmUpKey($searchQuery);
    }


}