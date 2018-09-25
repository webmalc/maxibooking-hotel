<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
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
}