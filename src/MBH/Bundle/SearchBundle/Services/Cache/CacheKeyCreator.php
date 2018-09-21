<?php


namespace MBH\Bundle\SearchBundle\Services\Cache;


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
        $tariff = $this->sharedDataFetcher->getFetchedTariff($searchQuery->getTariffId());
        $event = new GuestCombinationEvent();
        $event->setTariff($tariff);
        $this->dispatcher->dispatch(GuestCombinationEvent::CHILDREN_AGES, $event);
        $type = $event->getCombinationType();
        $keyCreator = $this->creatorFactory->getCacheKeyService($type);

        return $keyCreator->getKey($searchQuery);
    }


}