<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\CacheKeyInterface;
use MBH\Bundle\SearchBundle\Lib\Events\GuestCombinationEvent;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CacheKeyCreator
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SharedDataFetcher */
    private $sharedDataFetcher;

    /** @var  CacheKeyCreatorFactory */
    private $creatorFactory;

    /**
     * CacheKeyCreator constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param SharedDataFetcher $dataFetcher
     * @param CacheKeyCreatorFactory $cacheKeyCreatorFactory
     */
    public function __construct(EventDispatcherInterface $dispatcher, SharedDataFetcher $dataFetcher, CacheKeyCreatorFactory $cacheKeyCreatorFactory)
    {
        $this->dispatcher = $dispatcher;
        $this->sharedDataFetcher = $dataFetcher;
        $this->creatorFactory = $cacheKeyCreatorFactory;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return string
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function createKey(SearchQuery $searchQuery): string
    {
        return $this->getCreator($searchQuery->getTariffId())->getKey($searchQuery);
    }

    /**
     * @param SearchQuery $searchQuery
     * @return string
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function createWarmUpKey(SearchQuery $searchQuery): string
    {
        return $this->getCreator($searchQuery->getTariffId())->getWarmUpKey($searchQuery);
    }

    /**
     * @param string $tariffId
     * @return \MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\CacheKeyInterface
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    private function getCreator(string $tariffId): CacheKeyInterface
    {
        $tariff = $this->sharedDataFetcher->getFetchedTariff($tariffId);
        $event = new GuestCombinationEvent();
        $event->setTariff($tariff);
        $this->dispatcher->dispatch(GuestCombinationEvent::CHILDREN_AGES, $event);
        $type = $event->getCombinationType();

        return $this->creatorFactory->getCacheKeyService($type);
    }


}