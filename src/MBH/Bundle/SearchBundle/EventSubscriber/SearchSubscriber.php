<?php


namespace MBH\Bundle\SearchBundle\EventSubscriber;


use MBH\Bundle\SearchBundle\Lib\Events\SearchEvent;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use Monolog\Logger;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * SearchSubscriber constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents(): array
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