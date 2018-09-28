<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\Invalidate;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateAdapterFactory;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateInterface;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Predis\Client;

class SearchCacheInvalidator
{

    /** @var InvalidateAdapterFactory */
    private $adapterFactory;

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var Client */
    private $redis;

    /**
     * SearchCacheInvalidator constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param Client $redis
     * @param InvalidateAdapterFactory $factory
     */
    public function __construct(
        SearchResultCacheItemRepository $cacheItemRepository,
        Client $redis,
        InvalidateAdapterFactory $factory
    ) {
        $this->adapterFactory = $factory;
        $this->cacheItemRepository = $cacheItemRepository;
        $this->redis = $redis;
    }


    /**
     * @param InvalidateInterface $document
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \ReflectionException
     */
    public function invalidate(InvalidateInterface $document): void
    {
        $adapter = $this->adapterFactory->create($document);

        $begin = $adapter->getBegin();
        $end = $adapter->getEnd();
        $tariffIds = $adapter->getTariffIds();
        $roomTypeIds = $adapter->getRoomTypeIds();

        $this->invalidateCacheByData($begin, $end, $roomTypeIds, $tariffIds);
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime|null $end
     * @param array|null $roomTypeIds
     * @param array|null $tariffIds
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function invalidateCacheByData(
        \DateTime $begin,
        \DateTime $end = null,
        ?array $roomTypeIds = [],
        ?array $tariffIds = []
    ): void {
        if (null === $end) {
            $end = clone $begin;
        }

        $keys = $this->cacheItemRepository->fetchCachedKeys($begin, $end, $roomTypeIds, $tariffIds);
        $this->redis->del($keys);
        $this->cacheItemRepository->removeItemsByDates($begin, $end, $roomTypeIds, $tariffIds);
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