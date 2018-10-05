<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\BaseBundle\Document\CacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use Predis\Client;

class CacheSearchResults extends AbstractCacheSearchResult
{
    /**
     * @param SearchQuery $searchQuery
     * @param bool $hydrated
     * @return mixed
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function searchInCache(SearchQuery $searchQuery, $hydrated = false)
    {
        $result = null;
        $key = $this->createKey($searchQuery);
        $cacheResult = $this->redis->get($key);
        if ($cacheResult) {
            /** @var Result $result */
            $result = $hydrated ? $this->serializer->deserialize($cacheResult) : $this->serializer->decodeJsonToArray($cacheResult);
        }

        return $result;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return string
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    protected function createKey(SearchQuery $searchQuery): string
    {
        return $this->keyCreator->createKey($searchQuery);
    }


}