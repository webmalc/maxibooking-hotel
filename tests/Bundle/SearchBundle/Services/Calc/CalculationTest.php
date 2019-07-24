<?php


namespace Tests\Bundle\SearchBundle\Services\Calc;


use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class CalculationTest extends SearchWebTestCase
{

    public function setUp()
    {
        parent::setUp();
    }


    /** @dataProvider dataProviderRoomType
     * @param bool $isUseCategory
     * @param array $data
     */
    public function testCalcPrices(bool $isUseCategory, array $data): void
    {

        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        /** @var Hotel $hotel */
        $hotel = $dm->getRepository(Hotel::class)->findOneBy(['fullTitle' => $data['hotelName']]);
        $roomTypes = $hotel->getRoomTypes()->toArray();
        $searchRoomType = $this->getDocumentFromArrayByFullTitle($roomTypes, $data['searchRoomTypeName']);
        $hotelTariffs = $hotel->getTariffs()->toArray();
        $searchTariff = $this->getDocumentFromArrayByFullTitle($hotelTariffs, $data['searchTariffName']);


        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");

        $variants = $data['variants'];

        foreach ($variants as $variant) {
            $adults = $variant['adults'];
            $children = $variant['children'];

            $calcQuery = new SearchQuery();
            $calcQuery
                ->setTariffId($searchTariff->getId())
                ->setRoomTypeId($searchRoomType->getId())
                ->setBegin($begin)
                ->setEnd($end)
                ->setSearchHash(uniqid('', false));
            $searchConditions = new SearchConditions();
            $searchConditions
                ->setBegin($begin)
                ->setEnd($end);

            $calcQuery->setSearchConditions($searchConditions);


            if ($data['isExpectException'] ?? null) {
                $this->expectException($data['expectedException']);
            }
            if ($isUseCategory) {
                $clientConfigRepo = $this->createMock(ClientConfigRepository::class);
                $clientConfig = $this->createMock(ClientConfig::class);
                $clientConfig->method('getPriceRoundSign')->willReturn(2);
                $this->getContainer()->set('mbh_search.client_config_repository', $clientConfigRepo);
                $clientConfigRepo->method('fetchConfig')->willReturn($clientConfig);

                $roomTypeManger = $this->createMock(RoomTypeManager::class);
                $roomTypeManger->useCategories = $isUseCategory;
                $this->getContainer()->set('mbh.hotel.room_type_manager', $roomTypeManger);

            }

            $actual = $this->getContainer()->get('mbh_search.calculation')->calcPrices($calcQuery, $adults, $children);
            $key = $adults . '_' . $children;
            $this->assertArrayHasKey($key, $actual);
            $actualData = $actual[$key];
            $this->assertEquals($variant['total'], $actualData['total'], $key);
            $this->assertContainsOnlyInstancesOf(PackagePrice::class, $actualData['packagePrices']);
            foreach (range(0, $data['endOffset'] - $data['beginOffset'] - 1) as $index) {
                /** @var PackagePrice $packagePrice */
                $packagePrice = $actualData['packagePrices'][$index];
                $day = (clone $begin)->modify("+ $index days")->format('d_m_Y') . ' offset' . $index;
                $this->assertEquals($variant['priceByDay'][$index], $packagePrice->getPrice(), "Error in $key $day");
                $this->assertEquals($variant['tariffByDay'][$index], $packagePrice->getTariff()->getName(), "Error in $key $day");
                if ($promotionName = $variant['promotion'][$index] ?? null) {
                    $this->assertEquals($promotionName, $packagePrice->getPromotion()->getFullTitle());
                }

            }
        }


    }

    /**
     * @return iterable
     */
    public function dataProviderRoomType(): iterable
    {
        yield [
            false,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => 'Основной тариф',
                'hotelName' => 'Отель Волга',
                'beginOffset' => 8,
                'endOffset' => 13,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 1900 * 5,
                        'priceByDay' => [1900, 1900, 1900, 1900, 1900],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 3,
                        'children' => 0,
                        'total' => 2000 * 5,
                        'priceByDay' => [2000, 2000, 2000, 2000, 2000],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 3,
                        'children' => 2,
                        'total' => 2000 * 5 + (700 + 700) * 5,
                        'priceByDay' => [3400, 3400, 3400, 3400, 3400],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 5,
                        'children' => 0,
                        'total' => 2000 * 5 + (1500 + 1500) * 5,
                        'priceByDay' => [5000, 5000, 5000, 5000, 5000],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ]
                ],

            ]
        ];

        yield [
            false,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'hotelName' => 'Отель Волга',
                'beginOffset' => 2,
                'endOffset' => 9,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 1900 * 2 + 1890 * 4 + 1880,
                        'priceByDay' => [1900, 1900, 1890, 1890, 1890, 1890, 1880],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME],
                    ],
                    [
                        'adults' => 2,
                        'children' => 2,
                        'total' => 18780,
                        'priceByDay' => [2700, 2700, 2680, 2680, 2680, 2680, 2660],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                    ],
                ],

            ]
        ];

        yield [
            false,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'hotelName' => 'Гостиница Амур',
                'beginOffset' => 2,
                'endOffset' => 9,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 1900 * 2 + ((1890 * 4) * 50) / 100 + 1880 * 70 / 100,
                        'priceByDay' => [1900, 1900, 1890 / 2, 1890 / 2, 1890 / 2, 1890 / 2, (100 - 30) * 1880 / 100],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                        'promotion' => [null, null, 'SecondPromotion', 'SecondPromotion', 'SecondPromotion', 'SecondPromotion', 'FirstPromotion']
                    ],
                    [
                        'adults' => 2,
                        'children' => 2,
                        'total' => 5400 + 2680 * 2 + 2660 * 70 / 100,
                        'priceByDay' => [2700, 2700, 2680 / 2, 2680 / 2, 2680 / 2, 2680 / 2, (100 - 30) * 2660 / 100],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                        'promotion' => [null, null, 'SecondPromotion', 'SecondPromotion', 'SecondPromotion', 'SecondPromotion', 'FirstPromotion']
                    ],
                ],

            ]
        ];

        yield [
            false,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'hotelName' => 'Отель Волга',
                'beginOffset' => 2,
                'endOffset' => 9,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 1900 * 2 + 1890 * 4 + 1880,
                        'priceByDay' => [1900, 1900, 1890, 1890, 1890, 1890, 1880],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME],
                    ],
                    [
                        'adults' => 3,
                        'children' => 1,
                        'total' => 47010,
                        'priceByDay' => [6750, 6750, 6710, 6710, 6710, 6710, 6670],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                    ],
                    [
                        'adults' => 1,
                        'children' => 3,
                        'total' => 36510,
                        'priceByDay' => [5250, 5250, 5210, 5210, 5210, 5210, 5170],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                    ],
                ],

            ]
        ];

        yield [
            false,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'hotelName' => 'Отель Волга',
                'beginOffset' => 24,
                'endOffset' => 32,
                'isExpectException' => true,
                'expectedException' => PriceCachesMergerException::class,
                'variants' => [
                    [
                        'adults' => 3,
                        'children' => 2,
                    ],
                ],

            ]
        ];
        yield [
            true,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::THREE_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => 'Основной тариф',
                'hotelName' => 'Отель Волга',
                'beginOffset' => 8,
                'endOffset' => 13,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 2400 * 5,
                        'priceByDay' => [2400, 2400, 2400, 2400, 2400],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 3,
                        'children' => 0,
                        'total' => 2450 * 5,
                        'priceByDay' => [2450, 2450, 2450, 2450, 2450],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 3,
                        'children' => 2,
                        'total' => 2450 * 5 + (850 + 750) * 5,
                        'priceByDay' => [4050, 4050, 4050, 4050, 4050],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ],
                    [
                        'adults' => 5,
                        'children' => 0,
                        'total' => 2450 * 5 + (900 + 800) * 5,
                        'priceByDay' => [4150, 4150, 4150, 4150, 4150],
                        'tariffByDay' => ['Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф', 'Основной тариф'],
                    ]
                ],

            ]
        ];
        yield [
            true,
            [
                'searchRoomTypeName' => AdditionalRoomTypeData::TWO_PLUS_TWO_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'hotelName' => 'Отель Волга',
                'beginOffset' => 2,
                'endOffset' => 9,
                'variants' => [
                    [
                        'adults' => 1,
                        'children' => 0,
                        'total' => 2400 * 2 + 2390 * 4 + 2380,
                        'priceByDay' => [2400, 2400, 2390, 2390, 2390, 2390, 2380],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME],
                    ],
                    [
                        'adults' => 3,
                        'children' => 1,
                        'total' => 28520,
                        'priceByDay' => [4100, 4100, 4070, 4070, 4070, 4070, 4040],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                    ],
                    [
                        'adults' => 1,
                        'children' => 3,
                        'total' => 28170,
                        'priceByDay' => [4050, 4050, 4020, 4020, 4020, 4020, 3990],
                        'tariffByDay' => [
                            'Основной тариф',
                            'Основной тариф',
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::DOWN_TARIFF_NAME,
                            AdditionalTariffData::UP_TARIFF_NAME
                        ],
                    ],
                ],

            ]
        ];
    }

}