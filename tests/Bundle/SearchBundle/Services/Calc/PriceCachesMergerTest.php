<?php


namespace Tests\Bundle\SearchBundle\Services\Calc;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use MBH\Bundle\SearchBundle\Services\Calc\PriceCachesMerger;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class PriceCachesMergerTest extends SearchWebTestCase
{

    /** @var PriceCachesMerger */
    protected $service;

    /** @var DocumentManager */
    protected $dm;


    public function setUp()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        parent::setUp();
    }

    /** @dataProvider dataProvider
     * @param $data
     * @throws PriceCachesMergerException
     */
    public function testGetMergedPriceCaches($data): void
    {
        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");
        $dm = $this->dm;
        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);

        $roomTypes = $hotel->getRoomTypes()->toArray();
        $searchRoomType = $this->getDocumentFromArrayByFullTitle($roomTypes, $data['searchRoomTypeName']);

        $hotelTariffs = $hotel->getTariffs()->toArray();
        $searchTariff = $this->getDocumentFromArrayByFullTitle($hotelTariffs, $data['searchTariffName']);

        $isCategory = $data['isCategory'] ?? false;

        $calcQuery = new CalcQuery();
        $searchConditions = new SearchConditions();
        $searchConditions
            ->setAdditionalBegin(0)
            ->setAdditionalEnd(0)
            ->setBegin($begin)
            ->setEnd($end)
        ;
        $calcQuery
            ->setTariff($searchTariff)
            ->setRoomType($searchRoomType)
            ->setSearchBegin($begin)
            ->setSearchEnd($end)
            ->setConditionHash(uniqid('prefix', true))
            ->setConditionMaxBegin($calcQuery->getSearchBegin())
            ->setConditionMaxEnd($calcQuery->getSearchEnd())
            ->setSearchConditions($searchConditions)
        ;

        if ($data['expectException']) {
            $this->expectException(PriceCachesMergerException::class);
        }

        if ($isCategory) {
            $clientConfigRepo = $this->createMock(ClientConfigRepository::class);
            $clientConfig = $this->createMock(ClientConfig::class);
            $clientConfig->expects($this->any())->method('getUseRoomTypeCategory')->willReturn(true);
            $clientConfigRepo->expects($this->any())->method('fetchConfig')->willReturn($clientConfig);
            $roomTypeManger = $this->createMock(RoomTypeManager::class);
            $roomTypeManger->useCategories = $isCategory;
            $this->getContainer()->set('mbh_search.client_config_repository', $clientConfigRepo);
            $this->getContainer()->set('mbh.hotel.room_type_manager', $roomTypeManger);
        }

        $service = $this->getContainer()->get('mbh_search.price_caches_merger');


        $actual = $service->getMergedPriceCaches($calcQuery);
        $duration = $data['endOffset'] - $data['beginOffset'];
        $this->assertCount($duration, $actual);
        $matched = 0;
        //** TODO: Добавить дополнительно проверки дат и попробовать  */
        foreach ($actual as $value) {
            $cache = $value['data'];
            $actualSearchCacheTariffId = $value['searchTariffId'];
            $cacheDate = Helper::convertMongoDateToDate($cache['date']);
            $cacheOffset = (int)$cacheDate->diff(new \DateTime('midnight'))->format('%a');
            foreach ($data['expectedPriceCaches'] as $expectedPriceCache) {
                if ($expectedPriceCache['offset'] === $cacheOffset) {
                    $matched++;
                    $cacheTariffId = (string)$cache['tariff']['$id'];
                    /** @var Tariff $actualCacheTariff */
                    $actualCacheTariff = $this->getDocument(Tariff::class, $cacheTariffId);
                    //* $actualSearchCacheTariff тариф прайс кэша по которому производился поиск, на случай если тариф дочерний *//
                    $actualSearchCacheTariff = $this->dm->find(Tariff::class, $actualSearchCacheTariffId);
                    $this->assertEquals($expectedPriceCache['searchPriceCacheTariffName'], $actualSearchCacheTariff->getFullTitle());
                    $this->assertEquals($expectedPriceCache['priceCacheTariffName'], $actualCacheTariff->getName());
                }
            }
        }
        $this->assertEquals($matched, $duration, 'There is no all matched roomCaches');


    }

    /**
     * @param string $documentRepoName
     * @param string $documentId
     * @return object
     */
    private function getDocument(string $documentRepoName, string $documentId)
    {
        return $this->dm->find($documentRepoName, $documentId);
    }

    /**
     * @return iterable
     */
    public function dataProvider(): iterable
    {
        yield [
            [
                'expectException' => false,
                'beginOffset' => '8',
                'endOffset' => '15',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 11,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 12,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 13,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 14,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                ]
            ]
        ];

        yield [
            [
                'expectException' => false,
                'beginOffset' => '5',
                'endOffset' => '11',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ]
                ]
            ]
        ];

        yield [
            [

                'expectException' => false,
                'beginOffset' => '0',
                'endOffset' => '10',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 0,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 1,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 2,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 3,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                ]
            ]

        ];

        yield [
            [
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];


        yield [
            [
                'expectException' => false,
                'beginOffset' => '4',
                'endOffset' => '8',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ]
                ]
            ]
        ];

        yield [
            [
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];

        yield [
            [
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];

        yield [
            [
                'expectException' => true,
                'beginOffset' => '28',
                'endOffset' => '33',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => 'Основной тариф',
            ]
        ];

        yield ['withException' =>
            [
                'expectException' => true,
                'beginOffset' => '0',
                'endOffset' => '5',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
            ]
        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '8',
                'endOffset' => '15',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 11,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 12,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 13,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 14,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                ]
            ]
        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '5',
                'endOffset' => '11',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ]
                ]
            ]
        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '0',
                'endOffset' => '10',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 0,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 1,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 2,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 3,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                ]
            ]

        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];


        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '4',
                'endOffset' => '8',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ]
                ]
            ]
        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];

        yield [
            [
                'isCategory' => true,
                'expectException' => false,
                'beginOffset' => '24',
                'endOffset' => '28',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => AdditionalRoomTypeData::ONE_PLACE_ROOM_TYPE['fullTitle'],
                'searchTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME,
                        'searchPriceCacheTariffName' => AdditionalTariffData::CHILD_UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф',
                        'searchPriceCacheTariffName' => 'Основной тариф'
                    ]
                ]
            ]
        ];


    }

}