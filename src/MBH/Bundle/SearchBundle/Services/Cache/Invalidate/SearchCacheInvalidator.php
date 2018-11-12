<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\Invalidate;


use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessageFactory;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessageInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Predis\Client;

class SearchCacheInvalidator
{

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var Client */
    private $redis;

    /** @var InvalidateMessageFactory */
    private $invalidateAdapterFactory;

    /**
     * SearchCacheInvalidator constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param Client $redis
     * @param InvalidateMessageFactory $factory
     */
    public function __construct(
        SearchResultCacheItemRepository $cacheItemRepository,
        Client $redis,
        InvalidateMessageFactory $factory
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
        $message = $this->invalidateAdapterFactory->createMessage($invalidateQuery);
        $this->invalidate($message);
    }

    /**
     * @param InvalidateMessageInterface $message
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function invalidate(InvalidateMessageInterface $message): void
    {

        $begin = $message->getBegin();
        $end = $message->getEnd();
        $tariffIds = $message->getTariffIds();
        $roomTypeIds = $message->getRoomTypeIds();

        if (null !== $begin && null === $end) {
            $end = clone $begin;
        }

        $this->addWindowsPeriod($begin, $end);

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

        if (1 !== $deleted) {
            throw new SearchResultCacheException('No removed cache item from cache while invalidate');
        }
    }

    public function flushCache(): void
    {
        $this->redis->flushall();
        $this->cacheItemRepository->flushCache();
    }

    private function addWindowsPeriod($begin, $end): void
    {
        //** TODO: Temporary add window days (7 days) */
        if ($begin instanceof \DateTime) {
            $begin->modify('- 7 days');
        }
        if ($end instanceof \DateTime) {
            $end->modify('+ 7 days');
        }

    }

}