<?php


namespace Tests\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;

class AgeKeyTest extends WebTestCase
{


    /** @dataProvider dataProvider
     * @param $data
     * @param $expected
     */

    public function testGetNoChildrenAgeKey($data, $expected, $type): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $this->createFetcherMock($data);

        $keyCreator = $this->getContainer()->get('mbh_search.cache_key_no_children_ages');
        if ($type === 'no_warmup') {
            $actual = $keyCreator->getKey($searchQuery);
        }
        if ($type === 'warmup') {
            $actual = $keyCreator->getWarmUpKey($searchQuery);
        }
        $this->assertEquals($expected['no_age'], $actual);
    }

//    /**
//     * @param $data
//     * @param $expected
//     * @param $type
//     * @dataProvider dataProvider
//     * TODO: Enable az only
//     */
//    public function testChildrenAgeKey($data, $expected, $type): void
//    {
//        $searchQuery = $this->createSearchQuery($data);
//        $this->createFetcherMock($data);
//
//        $keyCreator = $this->getContainer()->get('mbh_search.cache_key_with_children_ages');
//        if ($type === 'no_warmup') {
//            $actual = $keyCreator->getKey($searchQuery);
//        }
//        if ($type === 'warmup') {
//            $actual = $keyCreator->getWarmUpKey($searchQuery);
//        }
//        $this->assertEquals($expected['with_age'], $actual);
//    }

    private function createFetcherMock($data)
    {
        $sharedDataFetcher = $this->createMock(SharedDataFetcher::class);
        $sharedDataFetcher->method('getFetchedTariff')->willReturn(
            (new Tariff())
                ->setId($data['tariffId'])
                ->setChildAge($data['tariffChildAge'])
                ->setInfantAge($data['tariffInfantAge'])
        );
        $sharedDataFetcher->method('getFetchedRoomType')->willReturn(
            (new RoomType())
                ->setId($data['roomTypeId'])
                ->setMaxInfants(2)
        );

        $this->getContainer()->set('mbh_search.shared_data_fetcher', $sharedDataFetcher);
    }

    private function createSearchQuery($data): SearchQuery
    {
        $searchQuery = new SearchQuery();
        $searchQuery
            ->setBegin($data['begin'])
            ->setEnd($data['end'])
            ->setRoomTypeId($data['roomTypeId'])
            ->setTariffId($data['tariffId'])
            ->setAdults($data['adults'])
            ->setChildren($data['children'])
            ->setChildrenAges($data['childrenAges'])
        ;

        return $searchQuery;
    }

    public function dataProvider(): iterable
    {
        return [
            [
                'data' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +1 day'),
                    'roomTypeId' => 'fakeRoomTypeId',
                    'tariffId' => 'fakeTariffId',
                    'adults' => 2,
                    'children' => 1,
                    'childrenAges' => [4],
                    'tariffInfantAge' => 2,
                    'tariffChildAge' => 14,
                    'withChildKey' => true,
                ],
                'expected' => [
                    'with_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'1'
                        .'_'.'groups'.'__'.'j1'
                    ,
                    'no_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'1',
                ],
                'no_warmup'

            ],
            [
                'data' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +1 day'),
                    'roomTypeId' => 'fakeRoomTypeId',
                    'tariffId' => 'fakeTariffId',
                    'adults' => 2,
                    'children' => 3,
                    'childrenAges' => [1, 8, 15],
                    'tariffInfantAge' => 2,
                    'tariffChildAge' => 14,
                    'withChildKey' => true,
                ],
                'expected' => [
                    'with_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'3'
                        .'_'.'groups'.'__'.'j1_t2'

                    ,
                    'no_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'3'
                        .'_'.'1',
                ],
                'no_warmup'
            ],
            [
                'data' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +1 day'),
                    'roomTypeId' => 'fakeRoomTypeId',
                    'tariffId' => 'fakeTariffId',
                    'adults' => 2,
                    'children' => 1,
                    'childrenAges' => [4],
                    'tariffInfantAge' => 2,
                    'tariffChildAge' => 14,
                    'withChildKey' => true,
                ],
                'expected' => [
                    'with_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'1'
                        .'_'.'groups'.'__'.'j1'
                    ,
                    'no_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'1',
                ],
                'warmup'

            ],
            [
                'data' => [
                    'begin' => new \DateTime('midnight'),
                    'end' => new \DateTime('midnight +1 day'),
                    'roomTypeId' => 'fakeRoomTypeId',
                    'tariffId' => 'fakeTariffId',
                    'adults' => 2,
                    'children' => 3,
                    'childrenAges' => [1, 8, 15],
                    'tariffInfantAge' => 2,
                    'tariffChildAge' => 14,
                    'withChildKey' => true,
                ],
                'expected' => [
                    'with_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'3'
                        .'_'.'groups'.'__'.'j1_t2'
                    ,
                    'no_age' =>
                        (new \DateTime('midnight'))->format('d.m.Y')
                        .'_'
                        .(new \DateTime('midnight +1 day'))->format('d.m.Y')
                        .'_'.'fakeRoomTypeId'
                        .'_'.'fakeTariffId'
                        .'_'.'2'
                        .'_'.'3',
                ],
                'warmup'
            ],
        ];
    }
}