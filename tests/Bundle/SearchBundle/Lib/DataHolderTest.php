<?php


namespace Tests\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class DataHolderTest extends SearchWebTestCase
{

    /** @var DataHolder */
    private $dataHolder;

    public function setUp()
    {
        parent::setUp();
        $this->dataHolder = $this->getContainer()->get('mbh_search.data_holder');

    }

    /** @dataProvider roomCacheDataProvider
     * @param $data
     */
    public function testGetNecessaryRoomCaches($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->dataHolder->getNecessaryRoomCaches($searchQuery);
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

    /** @dataProvider restrictionDataProvider
     * @param $data
     */
    public function testGetCheckNecessaryRestrictions($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->dataHolder->getCheckNecessaryRestrictions($searchQuery);
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

    /**
     * @param $data
     * @dataProvider accommodationDataProvider
     */
    public function testGetAccommodationRooms($data)
    {
        $searchQuery = $this->createSearchQuery($data);
        $actual = $this->dataHolder->getAccommodationRooms($searchQuery);
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

    public function roomCacheDataProvider(): iterable
    {
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

    public function restrictionDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => AdditionalTariffData::UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
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
                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
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

    public function accommodationDataProvider(): iterable
    {
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
                'beginOffset' => 3,
                'endOffset' => 9,
                'tariffFullTitle' => 'Основной тариф',
                'roomTypeFullTitle' => 'Стандартный двухместный',
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'noRoomNames' => [5,6,7,8,9,10]
                ]
            ]
        ];
    }
}