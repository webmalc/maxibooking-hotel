<?php


namespace Tests\Bundle\SearchBundle\Services\Calc;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\AdditionalRoomTypeData;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\RoomTypeCategoryData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\DataFixtures\MongoDB\AdditionalTariffData;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\PriceCachesMergerException;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQuery;
use MBH\Bundle\SearchBundle\Services\Calc\PriceCachesMerger;

class PriceCachesMergerTest extends WebTestCase
{

    /** @var PriceCachesMerger */
    protected $service;

    /** @var DocumentManager */
    protected $dm;

    public function setUp()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $this->service = $this->getContainer()->get('mbh_search.price_caches_merger');
        parent::setUp();
    }

    /** @dataProvider dataProvider */
    public function testGetMergedPriceCaches($data)
    {
        $begin = new \DateTime("midnight +{$data['beginOffset']} days");
        $end = new \DateTime("midnight +{$data['endOffset']} days");
        $dm = $this->dm;
        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);

        //** Категории как прикрутить ? */
        $roomTypes = $hotel->getRoomTypes()->toArray();
        $searchRoomType = $this->getDocumentFromArrayByFullTitle($roomTypes, $data['searchRoomTypeName']);

        $hotelTariffs = $hotel->getTariffs()->toArray();
        $searchTariff = $this->getDocumentFromArrayByFullTitle($hotelTariffs, $data['searchTariffName']);

        foreach ([false, true] as $isCategory) {
            $calcQuery = new CalcQuery();
            $calcQuery
                ->setTariff($searchTariff)
                ->setRoomType($searchRoomType)
                ->setSearchBegin($begin)
                ->setSearchEnd($end)
                ->setIsUseCategory($isCategory);

            if ($data['expectException']) {
                $this->expectException(PriceCachesMergerException::class);
            }
            $actual = $this->service->getMergedPriceCaches($calcQuery);
            $duration = $data['endOffset'] - $data['beginOffset'];
            $this->assertCount($duration, $actual);
            $matched = 0;
            //** TODO: Добавить дополнительно проверки дат и попробовать  */
            foreach ($actual as $cache) {
                $cacheDate = $cache['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                $cacheOffset = (int)$cacheDate->diff(new \DateTime('midnight'))->format('%d');
                foreach ($data['expectedPriceCaches'] as $expectedPriceCache) {
                    if ($expectedPriceCache['offset'] === $cacheOffset) {
                        $matched++;
                        $cacheTariffid = (string)$cache['tariff']['$id'];
                        /** @var Tariff $expectedTariff */
                        $expectedTariff = $this->getDocument(Tariff::class, $cacheTariffid);
                        $this->assertEquals($expectedPriceCache['priceCacheTariffName'], $expectedTariff->getName());
                    }
                }
            }
            $this->assertEquals($matched, $duration, 'There is no all matched roomCaches');
        }


    }

    private function getDocumentFromArrayByFullTitle(array $documents, string $documentFullTitle)
    {
        $filter = function ($document) use ($documentFullTitle) {
            return $document->getFullTitle() === $documentFullTitle;
        };
        $documentFiltered = array_filter($documents, $filter);

        return reset($documentFiltered);
    }

    private function getDocument(string $documentRepoName, string $documentId)
    {
        return $this->dm->find($documentRepoName, $documentId);
    }

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
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 11,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 12,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 13,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 14,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME],
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
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
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
                        'priceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 1,
                        'priceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 2,
                        'priceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 3,
                        'priceCacheTariffName' => 'Основной тариф'
                    ],
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
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
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф'
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
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => AdditionalTariffData::DOWN_TARIFF_NAME
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
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф'
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
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => AdditionalTariffData::UP_TARIFF_NAME
                    ],
                    [
                        'offset' => 27,
                        'priceCacheTariffName' => 'Основной тариф'
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


    }

}