<?php


namespace Tests\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
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
        $actual = $this->dataHolder->getNecessaryAccommodationRooms($searchQuery);
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

    /**
     * @param $data
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException
     * @dataProvider priceCachesDataProvider
     */
    public function testGetNecessaryPriceCaches($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $conditions = $searchQuery->getSearchConditions();
        $calcQuery = new CalcQuery();
        $tariff = $this->dm->find(Tariff::class, $searchQuery->getTariffId());
        $roomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $calcQuery
            ->setSearchBegin($searchQuery->getBegin())
            ->setSearchEnd($searchQuery->getEnd())
            ->setActualAdults($searchQuery->getActualAdults())
            ->setActualChildren($searchQuery->getActualChildren())
            ->setIsUseCategory(false)
            ->setTariff($tariff)
            ->setRoomType($roomType)
            //** TODO: Уточнить по поводу Promotion */
            /*->setPromotion()*/
            /** TODO: Это все необязательные поля, нужны исключительно для dataHolder чтоб получить все данные сразу */
            ->setConditionTariffs($conditions->getTariffs())
            ->setConditionRoomTypes($conditions->getRoomTypes())
            ->setConditionMaxBegin($conditions->getMaxBegin())
            ->setConditionMaxEnd($conditions->getMaxEnd())
            ->setConditionHash($conditions->getSearchHash());

        $actual = $this->dataHolder->getNecessaryPriceCaches($calcQuery, $calcQuery->getPriceTariffId());
        $a = 'b';


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



    }

    public function priceCachesDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 25,
                'tariffFullTitle' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => []
            ]
        ];
    }
}