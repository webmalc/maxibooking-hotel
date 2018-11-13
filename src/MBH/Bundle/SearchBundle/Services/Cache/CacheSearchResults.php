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
     * @return string
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    protected function createKey(SearchQuery $searchQuery): string
    {
        return $this->keyCreator->createKey($searchQuery);
    }


}