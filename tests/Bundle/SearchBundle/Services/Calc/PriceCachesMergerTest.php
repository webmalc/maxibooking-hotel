<?php


namespace Tests\Bundle\SearchBundle\Services\Calc;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
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

        $documentName = $data['searchRoomTypeName'];
        $filter = function ($document) use (&$documentName) {
            return $document->getName() === $documentName;
        };
        $roomTypeFiltered = array_filter($roomTypes, $filter);
        $searchRoomType = reset($roomTypeFiltered);

        $documentName = $data['searchTariffName'];
        $allTariffs = $hotel->getTariffs()->toArray();
        $searchTariffFiltered = array_filter($allTariffs, $filter);
        $searchTariff = reset($searchTariffFiltered);

        $calcQuery = new CalcQuery();
        $calcQuery
            ->setTariff($searchTariff)
            ->setRoomType($searchRoomType)
            ->setSearchBegin($begin)
            ->setSearchEnd($end)
            ->setIsUseCategory($data['isUseCategory']);

        if ($data['expectException']) {
            $this->expectException(PriceCachesMergerException::class);
        }
        $actual = $this->service->getMergedPriceCaches($calcQuery);
        $duration = $data['endOffset'] - $data['beginOffset'];
        $this->assertCount($duration, $actual);
        $matched = 0;
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 11,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 12,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 13,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 14,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
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
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 8,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 9,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 10,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'DownTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 4,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 5,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 6,
                        'priceCacheTariffName' => 'DownTariff'
                    ],
                    [
                        'offset' => 7,
                        'priceCacheTariffName' => 'DownTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'ChildUpTariff',
                'isUseCategory' => false,
                'expectedPriceCaches' => [
                    [
                        'offset' => 24,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 25,
                        'priceCacheTariffName' => 'UpTariff'
                    ],
                    [
                        'offset' => 26,
                        'priceCacheTariffName' => 'UpTariff'
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
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'Основной тариф',
                'isUseCategory' => false,
            ]
        ];

        yield ['withException' =>
            [
                'expectException' => true,
                'beginOffset' => '0',
                'endOffset' => '5',
                'searchHotelName' => 'nameOfHotel',
                'searchRoomTypeName' => 'ThreeAndThreeAndIndividualPriceRoomType',
                'searchTariffName' => 'UpTariff',
                'isUseCategory' => false,
            ]
        ];



    }

}