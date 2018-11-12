<?php


namespace Tests\Bundle\SearchBundle\Lib;


use Tests\Bundle\SearchBundle\NamesLibrary;
use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\DataHolder;
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
     * @dataProvider priceCachesDataProvider
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException
     */
    public function testGetNecessaryPriceCaches($data): void
    {
        $searchQuery = $this->createSearchQuery($data);
        $searchQuery->setAdults(1);
        $conditions = $searchQuery->getSearchConditions();
        $calcQuery = new CalcQuery();
        $tariff = $this->dm->find(Tariff::class, $searchQuery->getTariffId());
        $roomType = $this->dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $isCategory = $data['isCategory'];
        $conditionRoomType = new ArrayCollection();
        $isCategory ? $conditionRoomType->add($roomType->getCategory()) : $conditionRoomType->add($roomType);
        $conditionTariff = new ArrayCollection();
        $conditionTariff->add($tariff);
        $occupancies = $this->getContainer()->get('mbh_search.occupancy_determiner')->determine($searchQuery);
        $calcQuery
            ->setSearchBegin($searchQuery->getBegin())
            ->setSearchEnd($searchQuery->getEnd())
            ->setActualAdults($occupancies->getAdults())
            ->setActualChildren($occupancies->getChildren())
            ->setIsUseCategory($isCategory)
            ->setTariff($tariff)
            ->setRoomType($roomType)
            //** TODO: Уточнить по поводу Promotion */
            /*->setPromotion()*/
            /** TODO: Это все необязательные поля, нужны исключительно для dataHolder чтоб получить все данные сразу */
            ->setConditionTariffs($conditionTariff)
            ->setConditionRoomTypes($conditionRoomType)
            ->setConditionMaxBegin($conditions->getMaxBegin())
            ->setConditionMaxEnd($conditions->getMaxEnd())
            ->setConditionHash($conditions->getSearchHash());

        $actual = $this->dataHolder->getNecessaryPriceCaches($calcQuery, $calcQuery->getPriceTariffId());

        $expected = $data['expected'];
        $expectedCount = $expected['count'];
        $expectedRoomTypeName = $expected['RoomType'];
        $expectedTariffName = $expected['TariffName'];
        $expectedCategoryName = $expected['RoomTypeCategory'];

        if (null !== $expectedRoomTypeName) {
            $roomTypeId = $this->dm->getRepository(RoomType::class)->findOneBy(['fullTitle' => $expectedRoomTypeName])->getId();
        } else {
            $roomTypeId = null;
        }

        if (null !== $expectedCategoryName) {
            $categoryId = $this->dm->getRepository(RoomTypeCategory::class)->findOneBy(['fullTitle' => $expectedCategoryName])->getId();
        } else {
            $categoryId = null;
        }

        $this->assertCount($expectedCount, $actual);

        if ($roomTypeId) {
            $actualRoomTypeArray = array_map('\strval', array_column(array_column($actual, 'roomType'), '$id'));
            $diff = array_diff([$roomTypeId], $actualRoomTypeArray);
            $this->assertCount(0, $diff);
        }

        if ($categoryId) {
            $actualCategoryArray = array_map('\strval', array_column(array_column($actual, 'roomTypeCategory'), '$id'));
            $diff = array_diff([$categoryId], $actualCategoryArray);
            $this->assertCount(0, $diff);
        }

        $tariffId = $this->dm->getRepository(Tariff::class)->findOneBy(['fullTitle' => $expectedTariffName])->getId();
        $actualTariffArray = array_map('\strval', array_column(array_column($actual, 'tariff'), '$id'));
        $diff = array_diff([$tariffId], $actualTariffArray);
        $this->assertCount(0, $diff);

    }



    public function roomCacheDataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 0,
                'endOffset' => 5,
                'tariffFullTitle' => NamesLibrary::UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
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

    public function priceCachesDataProvider(): iterable
    {
        yield [
            [
                'isCategory' => false,
                'beginOffset' => 0,
                'endOffset' => 26,
                'tariffFullTitle' => NamesLibrary::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'count' => 18,
                    'TariffName' => NamesLibrary::UP_TARIFF_NAME,
                    'RoomType' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                    'RoomTypeCategory' => null
                ]
            ]
        ];
        yield [
            [
                'isCategory' => true,
                'beginOffset' => 0,
                'endOffset' => 26,
                'tariffFullTitle' => NamesLibrary::CHILD_UP_TARIFF_NAME,
                'roomTypeFullTitle' => NamesLibrary::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'hotelFullTitle' => 'Отель Волга',
                'expected' => [
                    'count' => 18,
                    'TariffName' => NamesLibrary::UP_TARIFF_NAME,
                    'RoomType' => null,
                    'RoomTypeCategory' => NamesLibrary::ADDITIONAL_PLACES_CATEGORY['fullTitle']
                ]
            ]
        ];
    }
}