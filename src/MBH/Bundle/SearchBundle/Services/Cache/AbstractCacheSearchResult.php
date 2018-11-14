<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\FilterResultException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilterInterface;
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

    /** @var ErrorResultFilterInterface */
    protected $filter;

    /**
     * SearchCache constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param ResultSerializer $serializer
     * @param Client $client
     * @param CacheKeyCreator $keyCreator
     */
    public function __construct(SearchResultCacheItemRepository $cacheItemRepository, ResultSerializer $serializer, Client $client, CacheKeyCreator $keyCreator, ErrorResultFilterInterface $filter)
    {
        $this->cacheItemRepository = $cacheItemRepository;
        $this->serializer = $serializer;
        $this->redis = $client;
        $this->keyCreator = $keyCreator;
        $this->dm = $cacheItemRepository->getDocumentManager();
        $this->filter = $filter;
    }


    public function searchInCache(SearchQuery $searchQuery, $hydrated = false)
    {
        $key = $this->createKey($searchQuery);
        $cacheResult = $this->redis->get($key);
        if (null !== $cacheResult) {
            $serializer = $this->serializer;
            if ('' === $cacheResult) {
                $exception = new SearchException();
                $errorResult = Result::createErrorResult($searchQuery, $exception);
                $cacheResult = $hydrated ? $errorResult : $serializer->normalize($errorResult);
            } else {
                /** @var Result $errorResult */
                $cacheResult = $hydrated ? $serializer->deserialize($cacheResult) : $serializer->decodeJsonToArray($cacheResult);
            }
        }

        return $cacheResult;
    }

    /**
     * @param Result $result
     * @param SearchQuery $searchQuery
     * @throws SearchResultCacheException
     */
    public function saveToCache(Result $result, SearchQuery $searchQuery): void
    {
        $cacheItem = SearchResultCacheItem::createInstance($result);
        $key = $this->createKey($searchQuery);
        $cacheItem->setCacheResultKey($key);
        $this->dm->persist($cacheItem);
        $this->dm->flush($cacheItem);

        try {
            $this->filter->filter($result, $searchQuery->getErrorLevel());
            $result->setCacheItemId($cacheItem->getId());
            $cacheResult = $this->serializer->serialize($result);
        } catch (FilterResultException $e) {
            $cacheResult = false;
        }

        $this->redis->set($key, $cacheResult);
    }

    abstract protected function createKey(SearchQuery $searchQuery): string;
}