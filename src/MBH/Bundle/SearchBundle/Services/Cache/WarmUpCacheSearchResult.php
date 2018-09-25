<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CacheWarmerException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
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
        throw new CacheWarmerException('No need search In cache when warmUp!');
    }

    /**
     * @param Result $result
     * @param SearchQuery $searchQuery
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function saveToCache(Result $result, SearchQuery $searchQuery): void
    {
        $key = $this->keyCreator->createWarmUpKey($searchQuery);
        $cacheItem = $this->cacheItemRepository->findOneBy(['cacheResultKey' => $key]);
        if (!$cacheItem) {
            $cacheItem = SearchResultCacheItem::createInstance($result);
            $cacheItem->setCacheResultKey($key);
        }

        $dm = $this->cacheItemRepository->getDocumentManager();
        $dm->persist($cacheItem);
        $dm->flush($cacheItem);
        $dm->clear($cacheItem);

        $result->setCacheItemId($cacheItem->getId());
        $this->redis->set($key, $this->serializer->serialize($result));
    }

}