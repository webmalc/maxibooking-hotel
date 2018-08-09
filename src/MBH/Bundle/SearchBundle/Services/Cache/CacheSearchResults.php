<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
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
     * SearchCache constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param ResultSerializer $serializer
     * @param Client $client
     */
    public function __construct(SearchResultCacheItemRepository $cacheItemRepository, ResultSerializer $serializer, Client $client)
    {
        $this->cacheItemRepository = $cacheItemRepository;
        $this->serializer = $serializer;
        $this->redis = $client;
    }


    public function searchInCache(SearchQuery $searchQuery): ?Result
    {
        $result = null;
        /** @var SearchResultCacheItem $cache */
        $key = SearchResultCacheItem::createRedisKey($searchQuery);
        $cacheResult = $this->redis->get($key);
        if ($cacheResult) {
            /** @var Result $result */
            $result = $this->serializer->deserialize($cacheResult);
        }

        return $result;

    }

    public function saveToCache(Result $result, SearchQuery $searchQuery): void
    {
        $cacheItem = SearchResultCacheItem::createInstance($result, $searchQuery);
        $this->redis->set($cacheItem->getCacheResultKey(), $this->serializer->serialize($result));
        $dm = $this->cacheItemRepository->getDocumentManager();
        $dm->persist($cacheItem);
        $dm->flush($cacheItem);

    }

    public function invalidateCache(\DateTime $begin, \DateTime $end = null): void
    {
        if (null === $end) {
            $end = clone $begin;
        }
        $this->cacheItemRepository->invalidateByDates($begin, $end);
    }

    public function flushCache(): void
    {
        $this->cacheItemRepository->flushCache();
    }

}