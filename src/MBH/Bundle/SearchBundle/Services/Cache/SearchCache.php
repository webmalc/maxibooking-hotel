<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchCache implements SearchCacheInterface
{

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /**
     * SearchCache constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     */
    public function __construct(SearchResultCacheItemRepository $cacheItemRepository)
    {
        $this->cacheItemRepository = $cacheItemRepository;
    }


    public function saveToCache(Result $result): void
    {
        $cacheItem = SearchResultCacheItem::createInstance($result);
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
            $result = $cache->getSerializedSearchResult();
        }

        return $result;

    }

    public function invalidateCache(\DateTime $begin, \DateTime $end = null)
    {
        if (null === $end) {
            $end = clone $begin;
        }
        $this->cacheItemRepository->invalidateByDates($begin, $end);
    }

}