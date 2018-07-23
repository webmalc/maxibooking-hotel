<?php


namespace Tests\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\RestrictionsFetchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Bundle\SearchBundle\NamesLibrary;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class RestrictionFetcherTest extends SearchWebTestCase
{

    /** @dataProvider restrictionDataProvider
     * @param $data
     */
    public function testFetchNecessaryDataSet($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $fetchQuery = RestrictionsFetchQuery::createInstanceFromSearchQuery($searchQuery);
        $actual = $this->getContainer()->get('mbh_search.restrictions_fetcher')->fetchNecessaryDataSet($fetchQuery);
        $expectedData = $data['expected'];
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($expectedData as $restrictionName => $offsets) {
            foreach ($offsets as $offsetIndex => $expectedValue) {
                $currentRestriction = $actual[$offsetIndex];
                $actualValue = $accessor->getValue($currentRestriction, "[{$restrictionName}]");
                $this->assertEquals($expectedValue, $actualValue);
            }
        }


    }

    public function restrictionDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => NamesLibrary::UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'minStayArrival' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => 5,
                        4 => 5,
                        5 => 5
                    ],
                    'close' => [
                        0 => null
                    ],

                ]
            ]
        ];

        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => NamesLibrary::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'minStayArrival' => [
                        0 => null,
                        1 => null,
                        2 => null,
                        3 => 5,
                        4 => 5,
                        5 => 5
                    ],
                    'close' => [
                        0 => null
                    ],

                ]
            ]
        ];

    }


}