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

class CacheSearchResults implements SearchCacheInterface
{

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var ResultSerializer */
    private $serializer;

    /** @var Client */
    private $redis;
    /**
     * @var CacheKeyCreator
     */
    private $keyCreator;

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
    }


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
        $key = $this->keyCreator->createKey($searchQuery);
        $cacheResult = $this->redis->get($key);
        if ($cacheResult) {
            /** @var Result $result */
            $result = $hydrated ? $this->serializer->deserialize($cacheResult) : $this->serializer->decodeJsonToArray($cacheResult);
        }

        return $result;
    }

    /**
     * @param Result $result
     * @param SearchQuery $searchQuery
     * @throws SearchResultCacheException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function saveToCache(Result $result, SearchQuery $searchQuery): void
    {
        $cacheItem = SearchResultCacheItem::createInstance($result);
        $key = $this->keyCreator->createKey($searchQuery);
        $cacheItem->setCacheResultKey($key);
        $dm = $this->cacheItemRepository->getDocumentManager();
        $dm->persist($cacheItem);
        $dm->flush($cacheItem);

        $result->setCacheItemId($cacheItem->getId());
        $this->redis->set($key, $this->serializer->serialize($result));
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime|null $end
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function invalidateCacheByDate(\DateTime $begin, \DateTime $end = null): void
    {
        if (null === $end) {
            $end = clone $begin;
        }

        $keys = $this->cacheItemRepository->fetchCachedKeys($begin, $end);
        $this->redis->del($keys);
        $this->cacheItemRepository->removeItemsByDates($begin, $end);

    }


    /**
     * @param SearchResultCacheItem $cacheItem
     * @throws SearchResultCacheException
     */
    public function invalidateCacheResultByCacheItem(SearchResultCacheItem $cacheItem): void
    {
        $key = $cacheItem->getCacheResultKey();
        $deleted = $this->redis->del([$key]);

        $dm = $this->cacheItemRepository->getDocumentManager();
        $dm->remove($cacheItem);
        $dm->flush($cacheItem);

        if (1 === $deleted) {
            throw new SearchResultCacheException('No removed cache item from cache while invalidate');
        }
    }

    public function flushCache(): void
    {
        $this->redis->flushall();
        $this->cacheItemRepository->flushCache();
    }

}