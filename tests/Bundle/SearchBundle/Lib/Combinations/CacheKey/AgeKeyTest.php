<?php


namespace Tests\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\ChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey\NoChildrenAgeKey;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class AgeKeyTest extends WebTestCase
{
    /** @dataProvider dataProvider
     * @param $data
     * @param $expected
     */
    public function testChildrenAgeGetKey($data, $expected): void
    {

        $searchQuery = $this->createSearchQuery($data);
        $keyCreator = new ChildrenAgeKey();
        $actual = $keyCreator->getKey($searchQuery);
        $this->assertEquals($expected['with_age'], $actual);
    }

    /** @dataProvider dataProvider
     * @param $data
     * @param $expected
     */
    public function testGetNoChildrenAgeKey($data, $expected): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $keyCreator = new NoChildrenAgeKey();
        $actual = $keyCreator->getKey($searchQuery);
        $this->assertEquals($expected['no_age'], $actual);
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
            ->setInfantAge($data['tariffInfantAge'])
            ->setChildAge($data['tariffChildAge']);

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
                        .'_'.'children_ages'.'_'.implode('_', [4])
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
                        .'_'.'children_ages'.'_'.implode('_', [1, 8, 14])
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

            ],
        ];
    }
}