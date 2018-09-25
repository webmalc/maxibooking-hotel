<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\ChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\NoChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationCreator;
use MBH\Bundle\SearchBundle\Lib\Events\GuestCombinationEvent;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\CacheKeyCreator;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CacheKeyCreatorTest extends WebTestCase
{
    /**
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     * @dataProvider dataProvider
     */
    public function testCreateKey($data): void
    {

        $sharedFetcher = $this->createMock(SharedDataFetcher::class);

        $tariffReflection = new \ReflectionClass(Tariff::class);
        $property = $tariffReflection->getProperty('id');
        $property->setAccessible(true);
        $sharedFetcher->expects($this->any())->method('getFetchedTariff')->willReturnCallback(function ($tariffId) use ($property){
            $tariff = new Tariff();
            $property->setValue($tariff, $tariffId);

            return $tariff;
        });

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(2))->method('dispatch')->willReturnCallback(
            function () {
                /** @var GuestCombinationEvent $event */
                $event = func_get_arg(1);
                $tariff = $event->getTariff();
                if ($tariff->getId() === 'with_children_ages_tariff_id') {
                    $event->setCombinationType(CombinationCreator::WITH_CHILDREN_AGES);
                }
            }
        );

        $searchQuery = new SearchQuery();
        $searchQuery->setTariffId($data['tariffId']);

        $container = $this->getContainer();
        $ages_key = $this->createMock(ChildrenAgeKey::class);
        $ages_key->expects($this->any())->method('getKey')->with($this->isInstanceOf(SearchQuery::class))->willReturnCallback(
            function ($searchQuery) {
                /** @var SearchQuery $searchQuery */
                $this->assertEquals('with_children_ages_tariff_id', $searchQuery->getTariffId());

                return 'fakeKey';
            });

        $container->set('mbh_search.cache_key_with_children_ages', $ages_key);

        $no_ages_key = $this->createMock(NoChildrenAgeKey::class);
        $no_ages_key->expects($this->any())->method('getKey')->with($this->isInstanceOf(SearchQuery::class))->willReturnCallback(
            function ($searchQuery) {
                /** @var SearchQuery $searchQuery */
                $this->assertEquals('no_children_ages_tariff_id', $searchQuery->getTariffId());

                return 'fakeKey';
            });

        $container->set('mbh_search.cache_key_no_children_ages', $no_ages_key);

        $keyFactory = $container->get('mbh_search.cache_key_creator_factory');

        $service = new CacheKeyCreator($dispatcher, $sharedFetcher, $keyFactory);
        $service->createKey($searchQuery);
        $service->createWarmUpKey($searchQuery);

    }

    public function dataProvider(): iterable
    {
        return [
            [
                'no_children' => [
                    'tariffId' => 'no_children_ages_tariff_id',
                ],
            ],
            [
                'with_children' => [
                    'tariffId' => 'with_children_ages_tariff_id',
                ],
            ],
        ];
    }
}