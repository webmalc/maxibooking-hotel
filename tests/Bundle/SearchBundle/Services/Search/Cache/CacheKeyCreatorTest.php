<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CacheKeyCreatorTest extends WebTestCase
{
    public function testCreateKey(): void
    {
        $container = $this->getContainer();

        $sharedFetcher = $this->createMock(SharedDataFetcher::class);
        $tariff = new Tariff();
        $sharedFetcher->method('getFetchedTariff')->willReturn($tariff);
        $container->set('mbh_search.shared_data_fetcher', $sharedFetcher);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);


    }
}