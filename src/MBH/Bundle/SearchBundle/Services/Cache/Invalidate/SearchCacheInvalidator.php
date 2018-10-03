<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\Invalidate;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateAdapterFactory;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateAdapterInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Predis\Client;

class SearchCacheInvalidator
{

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var Client */
    private $redis;

    /** @var InvalidateAdapterFactory */
    private $invalidateAdapterFactory;

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
        $this->cacheItemRepository = $cacheItemRepository;
        $this->redis = $redis;
        $this->invalidateAdapterFactory = $factory;
    }

    /**
     * @param InvalidateQuery $invalidateQuery
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    public function invalidateByQuery(InvalidateQuery $invalidateQuery): void
    {
        $adapter = $this->invalidateAdapterFactory->createAdapter($invalidateQuery);
        $this->invalidate($adapter);
    }

    /**
     * @param InvalidateAdapterInterface $adapter
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function invalidate(InvalidateAdapterInterface $adapter): void
    {

        $begin = $adapter->getBegin();
        $end = $adapter->getEnd();
        $tariffIds = $adapter->getTariffIds();
        $roomTypeIds = $adapter->getRoomTypeIds();

        if (null !== $begin && null === $end) {
            $end = clone $begin;
        }

        $keys = $this->cacheItemRepository->fetchCachedKeys($begin, $end, $roomTypeIds, $tariffIds);
        if (\count($keys)) {
            $this->redis->del($keys);
            $this->cacheItemRepository->removeItemsByDates($begin, $end, $roomTypeIds, $tariffIds);
        }

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