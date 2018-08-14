<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Search\SearchResultComposer;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchResultComposerTest extends SearchWebTestCase
{
    /** @var SearchResultComposer */
    private $searchComposer;

    public function setUp()
    {
        parent::setUp();
        $this->searchComposer = $this->getContainer()->get('mbh_search.result_composer');
    }

    /** @dataProvider dataProvider */
    public function testComposeResult($data)
    {
        $searchQuery = $this->createSearchQuery($data);
        $searchQuery->getSearchConditions()->setId('fakeId');
        /** @var Result $actual */
        $actual = $this->searchComposer->composeResult($searchQuery);
        $expected = $data['expected'];
        /** TODO: Добавить всякой фигни */
        $this->assertEquals($expected['minCache'], $actual->getMinRoomsCount());
    }

    public function dataProvider(): iterable
    {

        yield [
            [
                'beginOffset' => 3,
                'endOffset' => 8,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'adults' => 1,
                'expected' => [
                    'prices' => ['1_0' => 11280],
                    'minCache' => 5
                ],
            ]
        ];

//        yield [
//            [
//                'beginOffset' => 10,
//                'endOffset' => 16,
//                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
//                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
//                'hotelFullTitle' => 'Отель Волга',
//                'adults' => 1,
//                'expected' => [
//                    'prices' => ['1_0' => 11280],
//
//                ],
//            ]
//        ];

    }

}