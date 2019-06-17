<?php


namespace Tests\Bundle\SearchBundle\Services\Data;


use function count;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
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
        $actual = $this->getContainer()->get('mbh_search.data_manager')->fetchData($searchQuery, RoomCacheRawFetcher::NAME);

        $expectedNoQuoted = $data['expected']['noQuoted'];
        $expectedQuoted = $data['expected']['quoted'];

        foreach (range($data['beginOffset'], $data['endOffset'] - 1) as $offset) {
            $currentDateKey = (new \DateTime('midnight'))->modify("+{$offset} days")->format('d-m-Y');
            $noQuotedValue = null;
            $quotedValue = null;
            foreach ($actual as $roomCache) {
                $roomCacheDate = Helper::convertMongoDateToDate($roomCache['date'])->format('d-m-Y');
                if ($roomCacheDate == $currentDateKey) {
                    if (!isset($roomCache['tariff']) || $roomCache['tariff'] === null) {
                        $noQuotedValue = $roomCache['leftRooms'];
                    } else {
                        $quotedValue = $roomCache['leftRooms'];
                    }
                }
            }
            $this->assertEquals($expectedNoQuoted[$offset], $noQuotedValue);
            $this->assertEquals($expectedQuoted[$offset], $quotedValue);
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
                    'noQuoted' => [
                        0 => null,
                        1 => null,
                        2 => 12,
                        3 => 12,
                        4 => 12
                    ],
                    'quoted' => [
                        0 => null,
                        1 => null,
                        2 => 8,
                        3 => 8,
                        4 => null
                    ]
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
                    'noQuoted' => [
                        0 => null,
                        1 => null,
                        2 => 5,
                        3 => 5,
                        4 => 5
                    ],
                    'quoted' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => null,
                        4 => null
                    ]
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
                    'quoted' => [
                        0 => null,
                        1 => null,
                        2 => 8,
                        3 => 8,
                        4 => null
                    ],
                    'noQuoted' => [
                        0 => null,
                        1 => null,
                        2 => 12,
                        3 => 12,
                        4 => 12
                    ]

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
                    'quoted' => [
                        0 => null,
                        1 => null,

                    ],
                    'noQuoted' => [
                        0 => null,
                        1 => null,
                    ]
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
                    'quoted' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => null,
                        4 => null
                    ],
                    'noQuoted' => [
                        0 => 2,
                        1 => 1,
                        2 => 0,
                        3 => 6,
                        4 => 5,
                    ]
                ],
            ]
        ];
    }
}