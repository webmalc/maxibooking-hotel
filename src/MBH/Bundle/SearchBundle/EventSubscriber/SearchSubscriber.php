<?php


namespace MBH\Bundle\SearchBundle\EventSubscriber;


use MBH\Bundle\SearchBundle\Lib\Events\SearchEvent;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{

    /** @var DataManager */
    private $dataManager;
    /**
     * @var Client
     */
    private $client;

    /**
     * SearchSubscriber constructor.
     * @param DataManager $dataManager
     * @param Client $client
     */
    public function __construct(DataManager $dataManager, Client $client)
    {
        $this->dataManager = $dataManager;
        $this->client = $client;
    }

    public static function getSubscribedEvents()
    {
        return [
            SearchEvent::SEARCH_ASYNC_END => [
                'clearMemory',
                0,
            ],
        ];
    }

    public function clearMemory(SearchEvent $event): void
    {
        $conditions = $event->getSearchConditions();
        $hash = $conditions->getSearchHash();
        $this->client->set($hash, $hash, 'EX', 1800);
        $this->client->sadd(SearchEvent::SEARCH_ASYNC_END, [$hash]);
    }



}