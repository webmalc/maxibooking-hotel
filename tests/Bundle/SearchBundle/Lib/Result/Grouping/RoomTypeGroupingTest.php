<?php


namespace Tests\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Grouping\RoomTypeGrouping;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class RoomTypeGroupingTest extends SearchWebTestCase
{
    /** @dataProvider dataProvider
     * @param iterable $data
     */
    public function testGroup(iterable $data): void
    {
        $source = $data['source'];
        $expected = $data['expected'];
        $searchResults = [];
        foreach ($source as $value) {
            $result = $this->createMock(Result::class);
            $resultRoomType = $this->createMock(ResultRoomType::class);
            $resultRoomType->expects($this->any())->method('getId')->willReturn($value['roomTypeId']);
            $result->expects($this->any())->method('getResultRoomType')->willReturn($resultRoomType);
            $result->expects($this->any())->method('getBegin')->willReturn(new \DateTime("midnight + {$value['beginOffset']} days"));
            $result->expects($this->any())->method('getEnd')->willReturn(new \DateTime("midnight + {$value['endOffset']} days"));
            $searchResults[] = $result;
        }

        $service = new RoomTypeGrouping();
        $actual = $service->group($searchResults);
        $actualResults = array_reduce($actual, function ($carry, $item) {
            $resultsByDate = $item['results'];
            $count = 0;
            foreach ($resultsByDate as $results) {
                $count += \count($results);
            }

            return $carry + $count;
        });
        $this->assertEquals($expected['results'], $actualResults);
        $this->assertArraySimilar($expected['groups'], array_keys($actual));

    }

    public function dataProvider()
    {
        yield [
            [
                'source' => [
                    [
                        'roomTypeId' => 'firstRoomTypeId',
                        'tariffName' => 'TariffOne',
                        'beginOffset' => '1',
                        'endOffset' => '3'
                    ],
                    [
                        'roomTypeId' => 'firstRoomTypeId',
                        'tariffName' => 'TariffTwo',
                        'beginOffset' => '1',
                        'endOffset' => '3'
                    ],
                    [
                        'roomTypeId' => 'firstRoomTypeId',
                        'tariffName' => 'TariffTwo',
                        'beginOffset' => '2',
                        'endOffset' => '4'
                    ],
                    [
                        'roomTypeId' => 'secondRoomTypeId',
                        'tariffName' => 'TariffTwo',
                        'beginOffset' => '1',
                        'endOffset' => '3'
                    ],
                ],
                'expected' => [
                    'results' => 4,
                    'groups' => [
                        'firstRoomTypeId', 'secondRoomTypeId'
                    ]
                ]
            ]
        ];
    }
}