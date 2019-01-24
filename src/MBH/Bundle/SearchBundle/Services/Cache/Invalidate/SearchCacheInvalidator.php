<?php


namespace MBH\Bundle\SearchBundle\Services\Cache\Invalidate;


use Doctrine\MongoDB\ArrayIterator;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessageFactory;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateMessageInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Lib\Events\InvalidateKeysEvent;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SearchCacheInvalidator
{
    public const INVALIDATOR_KEY_INVALIDATE = 'invalidator.key.invalidate';

    private const INVALIDATOR_MAX_KEYS_THRESHOLD = 10000;

    /** @var SearchResultCacheItemRepository */
    private $cacheItemRepository;

    /** @var Client */
    private $redis;

    /** @var InvalidateMessageFactory */
    private $invalidateAdapterFactory;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * SearchCacheInvalidator constructor.
     * @param SearchResultCacheItemRepository $cacheItemRepository
     * @param Client $redis
     * @param InvalidateMessageFactory $factory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        SearchResultCacheItemRepository $cacheItemRepository,
        Client $redis,
        InvalidateMessageFactory $factory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->cacheItemRepository = $cacheItemRepository;
        $this->redis = $redis;
        $this->invalidateAdapterFactory = $factory;
        $this->dispatcher = $dispatcher;
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

        //** TODO: Temporary add window days (7 days) */
        $this->addWindowsPeriod($begin, $end);

        /** @var ArrayIterator $keysIterator */
        $keysIterator = $this->cacheItemRepository->fetchCachedKeys($begin, $end, $roomTypeIds, $tariffIds);
        if (($keysAmount = $keysIterator->count()) && $keysAmount < self::INVALIDATOR_MAX_KEYS_THRESHOLD) {
            $keys = $keysIterator->toArray();
            $this->redis->del($keys);
            $this->cacheItemRepository->removeItemsByDates($begin, $end, $roomTypeIds, $tariffIds);

            $event = new InvalidateKeysEvent();
            $event->setKeys($keys);
            $this->dispatcher->dispatch(static::INVALIDATOR_KEY_INVALIDATE, $event);
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

        $event = new InvalidateKeysEvent();
        $event->setKeys([$key]);
        $this->dispatcher->dispatch(static::INVALIDATOR_KEY_INVALIDATE, $event);
    }

    public function flushCache(): void
    {
        $this->redis->flushall();
        $this->cacheItemRepository->flushCache();
    }

    private function addWindowsPeriod($begin, $end): void
    {
        $dm = $this->cacheItemRepository->getDocumentManager();
        $config = $dm->getRepository(ClientConfig::class)->fetchConfig();
        if ($config->getSearchWindows()) {
            if ($begin instanceof \DateTime) {
                $begin->modify('- 7 days');
            }
            if ($end instanceof \DateTime) {
                $end->modify('+ 7 days');
            }
        }


    }

}