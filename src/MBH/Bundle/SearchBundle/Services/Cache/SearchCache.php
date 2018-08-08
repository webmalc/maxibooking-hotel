<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;

class SearchCache implements SearchCacheInterface
{

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var ResultSerializer */
    private $serializer;

    /**
     * SearchCache constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     */
    public function __construct(SearchResultCacheItemRepository $cacheItemRepository, ResultSerializer $serializer)
    {
        $this->cacheItemRepository = $cacheItemRepository;
        $this->serializer = $serializer;
    }


    public function saveToCache(Result $result): void
    {
        $cacheItem = SearchResultCacheItem::createInstance($result, $this->serializer);
        $dm = $this->cacheItemRepository->getDocumentManager();
        $dm->persist($cacheItem);
        $dm->flush($cacheItem);

    }

    public function searchInCache(SearchQuery $searchQuery): ?Result
    {
        $result = null;
        /** @var SearchResultCacheItem $cache */
        $cache = $this->cacheItemRepository->fetchBySearchQuery($searchQuery);
        if ($cache) {
            $json = $cache['serializedSearchResult'];
            $result = $this->serializer->deserialize($json);
        }

        return $result;

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