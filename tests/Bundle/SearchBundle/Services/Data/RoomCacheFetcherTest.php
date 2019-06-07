<?php


namespace Tests\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\SearchBundle\Lib\Data\RoomCacheFetchQuery;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\RoomCacheRawFetcher;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class RoomCacheFetcherTest extends SearchWebTestCase
{
    /** @dataProvider roomCacheDataProvider
     * @param $data
     * @throws DataFetchQueryException
     */
    public function testFetchNecessaryDataSet($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
//        $fetchQuery = RoomCacheFetchQuery::createInstanceFromSearchQuery($searchQuery);
//        $actual = $this->getContainer()->get('mbh_search.room_cache_fetcher')->fetchNecessaryDataSet($fetchQuery);
        $actual = $this->getContainer()->get('mbh_search.data_manager')->fetchData($searchQuery, RoomCacheRawFetcher::NAME);
        $expectedData = $data['expected'];
        $actualWithDate = [];
        foreach ($actual as $roomCache) {
            $actualWithDate[Helper::convertMongoDateToDate($roomCache['date'])->format('d-m-Y')] = $roomCache;
        }

        $this->assertCount(\count(array_filter($expectedData, '\strlen')), $actual);

        foreach ($expectedData as $roomCacheOffset => $roomCacheValue) {
            if (null !== $roomCacheValue) {
                $currentDateKey = (new \DateTime('midnight'))->modify("+{$roomCacheOffset} days")->format('d-m-Y');
                $this->assertEquals($roomCacheValue, $actualWithDate[$currentDateKey]['leftRooms']);
            }
        }
    }

    public function roomCacheDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => null,
                    1 => null,
                    2 => 8,
                    3 => 8,
                    4 => 12
                ],
            ]
        ];


        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => null,
                    1 => null,
                    2 => 5,
                    3 => 5,
                    4 => 5
                ],
            ]
        ];

        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => null,
                    1 => null,
                    2 => 8,
                    3 => 8,
                    4 => 12
                ],
            ]
        ];


        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 2,
                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => null,
                    1 => null
                ],
            ]
        ];

        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    0 => 2,
                    1 => 1,
                    2 => 0,
                    3 => 6,
                    4 => 5,
                ],
            ]
        ];
    }
}