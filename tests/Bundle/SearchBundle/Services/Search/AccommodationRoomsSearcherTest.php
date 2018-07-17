<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AccommodationRoomsSearcherTest extends SearchWebTestCase
{
    /**
     * @param $data
     * @dataProvider accommodationDataProvider
     */
    public function testSearch($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->getContainer()->get('mbh_search.accommodation_room_searcher')->search($searchQuery);
        $expected = $data['expected'];
        $noRoomNames = $expected['noRoomNames'];
        $actualRoomNames = array_column($actual, 'fullTitle');
        if (empty($noRoomNames)) {
            $this->assertCount(10, $actualRoomNames);
        }
        $this->assertCount(10 - \count($noRoomNames), $actual);
        foreach ($noRoomNames as $noRoomName) {
            $this->assertNotContains((string)$noRoomName, $actualRoomNames);
        }
    }

    public function accommodationDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 25,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => range(1,10)
                ]
            ]
        ];

        yield [
            [
                'beginOffset' => 3,
                'endOffset' => 10,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => [1,8,9]
                ]
            ]
        ];
        yield [
            [
                'beginOffset' => 3,
                'endOffset' => 12,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => [1,7,8,9]
                ]
            ]
        ];

        yield [
            [
                'beginOffset' => 9,
                'endOffset' => 12,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => [7]
                ]
            ]
        ];

        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 25,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'ThreePlace',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => []
                ]
            ]
        ];
    }


}