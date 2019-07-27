<?php


namespace Tests\Bundle\SearchBundle\Lib\Result\Grouping;


use Generator;
use MBH\Bundle\SearchBundle\Lib\Result\Grouping\RoomTypeGrouping;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class RoomTypeGroupingTest extends SearchWebTestCase
{
    /**
     * @dataProvider dataProvider
     * @param iterable $data
     * @throws \Exception
     */
    public function testGroup(iterable $data): void
    {
        $source = $data['source'];
        $expected = $data['expected'];
        $searchResults = [];
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        foreach ($source as $value) {
            $result = $this->createMock(Result::class);
            $result->method('getRoomType')->willReturn($value['roomTypeId']);
            $result->method('getBegin')->willReturn(new \DateTime("midnight + {$value['beginOffset']} days"));
            $result->method('getEnd')->willReturn(new \DateTime("midnight + {$value['endOffset']} days"));
            $searchResults[] = $serializer->normalize(
                $result,
                [
                    'attributes' => ['id', 'roomType', 'begin', 'end']
                ]
            );
        }

        $service = new RoomTypeGrouping();
        $actual = $service->group($searchResults);
        $actualResults = array_reduce($actual, static function ($carry, $item) {
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

    public function dataProvider(): ?Generator
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