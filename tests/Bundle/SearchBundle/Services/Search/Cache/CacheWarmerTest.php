<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use MBH\Bundle\SearchBundle\Services\GuestCombinator;
use MBH\Bundle\SearchBundle\Services\Search\SearchCombinations;
use MBH\Bundle\SearchBundle\Services\Search\WarmUpSearcher;
use MBH\Bundle\SearchBundle\Services\SearchConditionsCreator;
use MBH\Bundle\SearchBundle\Services\SearchCombinationsGenerator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class CacheWarmerTest extends WebTestCase
{

    public function testWarmUp(): void
    {
        $container = $this->getContainer();


        $combinations1 = [
            [
                'adults' => 1,
                'children' => 0,
            ],
            [
                'adults' => 1,
                'children' => 1,
            ],

        ];

        $combinations2 = [
            [
                'adults' => 1,
                'children' => 2,
                'childrenAges' => [4, 3],
            ],
            [
                'adults' => 1,
                'children' => 1,
                'childrenAges' => [3],
            ],
        ];

        $dateMultiplier = 2 * (CacheWarmer::MAX_BOOKING_LENGTH - CacheWarmer::MIN_BOOKING_LENGTH + 1);

        $combinationInterface1 = $this->createMock(CombinationInterface::class);
        $combinationInterface1->expects($this->exactly(1 * $dateMultiplier))->method('getCombinations')->willReturn(
            $combinations1
        );
        $combinationInterface1->expects($this->exactly(2 * $dateMultiplier))->method('getTariffIds')->willReturn(
            ['no_child_id1', 'no_child_id2']
        );

        $combinationInterface2 = $this->createMock(CombinationInterface::class);
        $combinationInterface2->expects($this->exactly(1 * $dateMultiplier))->method('getCombinations')->willReturn(
            $combinations2
        );
        $combinationInterface2->expects($this->exactly(2 * $dateMultiplier))->method('getTariffIds')->willReturn(
            ['child_id1']
        );

        $combinationInterfaces = [
            $combinationInterface1,
            $combinationInterface2,
        ];

        $combinator = $this->createMock(GuestCombinator::class);
        $combinator->expects($this->exactly(1))->method('getCombinations')->willReturn(
            $combinationInterfaces
        );
        $container->set('mbh_search.combinator', $combinator);

        $conditionCreator = $this->createMock(SearchConditionsCreator::class);
        $conditionCreator->expects($this->exactly(4 * $dateMultiplier))->method(
            'createSearchConditions'
        )->willReturnCallback(
            function ($conditionsData) {
                $this->assertInternalType('array', $conditionsData);

                return new SearchConditions();
            }
        );
        $container->set('mbh_search.search_condition_creator', $conditionCreator);


        $queryGenerator = $this->createMock(SearchCombinationsGenerator::class);
        $queryGenerator->expects($this->exactly(4 * $dateMultiplier))->method('generate')->willReturnCallback(function() {

            $combinations = new SearchCombinations();
            $combinations->setDates(['period1' => ['begin' => new \DateTime('midnight'), 'end' => new \DateTime('midnight +1 day')]]);
            $tariffRoomType = [
                ['tariffId' => 'fakeTariffId1', 'roomTypeId' => 'fakeRoomTypeId1', 'restrictionTariffId' => 'fakeTariffId1'],
                ['tariffId' => 'fakeTariffId1', 'roomTypeId' => 'fakeRoomTypeId2', 'restrictionTariffId' => 'fakeTariffId1']
            ];
            $combinations->setTariffRoomTypeCombinations($tariffRoomType);

            return $combinations;
        });
        $container->set('mbh_search.search_combinations_generator', $queryGenerator);

        $producer = $this->createMock(ProducerInterface::class);
        $container->set('old_sound_rabbit_mq.warm_up_search_producer', $producer);



        $warmer = $this->getContainer()->get('mbh_search.cache_warmer');
        $warmer->warmUp(new \DateTime('midnight'), new \DateTime('midnight + 1 days'));
    }

}