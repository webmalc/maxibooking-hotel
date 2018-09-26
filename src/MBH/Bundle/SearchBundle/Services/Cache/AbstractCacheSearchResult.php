<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use Predis\Client;

abstract class AbstractCacheSearchResult implements SearchCacheInterface
{
    /** @var SearchResultCacheItemRepository */
    protected $cacheItemRepository;

    /** @var ResultSerializer */
    protected $serializer;

    /** @var Client */
    protected $redis;
    /**
     * @var CacheKeyCreator
     */
    protected $keyCreator;

    /** @var DocumentManager */
    protected $dm;

    /**
     * SearchCache constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param ResultSerializer $serializer
     * @param Client $client
     * @param CacheKeyCreator $keyCreator
     */
    public function __construct(SearchResultCacheItemRepository $cacheItemRepository, ResultSerializer $serializer, Client $client, CacheKeyCreator $keyCreator)
    {
        $this->cacheItemRepository = $cacheItemRepository;
        $this->serializer = $serializer;
        $this->redis = $client;
        $this->keyCreator = $keyCreator;
        $this->dm = $cacheItemRepository->getDocumentManager();
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
        $cacheItem = SearchResultCacheItem::createInstance($result);
        $key = $this->keyCreator->createKey($searchQuery);
        $cacheItem->setCacheResultKey($key);
        $this->dm->persist($cacheItem);
        $this->dm->flush($cacheItem);

        $result->setCacheItemId($cacheItem->getId());
        $this->redis->set($key, $this->serializer->serialize($result));
    }
}