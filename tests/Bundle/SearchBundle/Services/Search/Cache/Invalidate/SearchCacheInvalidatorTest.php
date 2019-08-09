<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache\Invalidate;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateQuery;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;

class SearchCacheInvalidatorTest extends WebTestCase
{
    /** @var array */
    protected const OFFSETS = [[0, 6], [4, 10], [8, 13]];

    /** @dataProvider priceCacheInvalidateProvider */
    public function testInvalidatePriceCache($data): void
    {
        $dateOffset = $data['offset'];
        $date = new \DateTime("midnight + ${dateOffset} days");
        $container = $this->getContainer();
        $dm = $container->get('doctrine.odm.mongodb.document_manager');
        $tariffId = $dm->getRepository(Tariff::class)->findOneBy(['fullTitle' => 'UpTariff'])->getId();
        $isUseCategory = $container->get('mbh.hotel.room_type_manager')->getIsUseCategories();
        $excludeRoomTypeType = !$isUseCategory ? 'roomTypeCategory' : 'roomType';
        $priceCache = $dm->getRepository(PriceCache::class)->findOneBy(
            ['date' => $date, 'tariff.id' => $tariffId, $excludeRoomTypeType => null]
        );
        $invalidateQuery = new InvalidateQuery();
        $invalidateQuery->setObject($priceCache)
            ->setType(InvalidateQuery::PRICE_CACHE);

        $this->invalidate($invalidateQuery, $data['expected']['keysNumToInvalidate']);
    }

    /**
     * @param $data
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     * @dataProvider restrictionInvalidateProvider
     */
    public function testInvalidateRestriction($data): void
    {
        $dateOffset = $data['offset'];
        $date = new \DateTime("midnight + ${dateOffset} days");
        $restriction = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository(
            Restriction::class
        )->findOneBy(
            ['date' => $date]
        );
        $invalidateQuery = new InvalidateQuery();
        $invalidateQuery->setObject($restriction)
            ->setType(InvalidateQuery::RESTRICTIONS);
        $this->invalidate($invalidateQuery, $data['expected']['keysNumToInvalidate']);
    }

    /** @dataProvider roomCacheInvalidateProvider */
    public function testInvalidateRoomCache($data)
    {
        $dateOffset = $data['offset'];
        $date = new \DateTime("midnight + ${dateOffset} days");
        $roomCache = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository(
            RoomCache::class
        )->findOneBy(['date' => $date]);

        $invalidateQuery = new InvalidateQuery();
        $invalidateQuery->setObject($roomCache)
            ->setType(InvalidateQuery::ROOM_CACHE);

        $this->invalidate($invalidateQuery, $data['expected']['keysNumToInvalidate']);
    }

    /**
     * @param $data
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     * @throws \ReflectionException
     * @dataProvider tariffInvalidateProvider
     */
    public function testInvalidateTariff($data): void
    {
        $tariff = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository(
            Tariff::class
        )->findOneBy(
            ['fullTitle' => 'UpTariff']
        );

        $invalidateQuery = new InvalidateQuery();
        $invalidateQuery->setObject($tariff)
            ->setType(InvalidateQuery::TARIFF);

        $this->invalidate($invalidateQuery, $data['expected']['keysNumToInvalidate']);
    }

    /**
     * @param $data
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     * @throws \ReflectionException
     * @dataProvider roomTypeInvalidateProvider
     */
    public function testInvalidateRoomType($data): void
    {
        $roomType = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository(
            RoomType::class
        )->findOneBy(
            []
        );

        $invalidateQuery = new InvalidateQuery();
        $invalidateQuery->setObject($roomType)
            ->setType(InvalidateQuery::ROOM_TYPE);
        $this->invalidate($invalidateQuery, $data['expected']['keysNumToInvalidate']);
    }

    public function testInvalidatePriceGenerator()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $hotel = $dm->getRepository(Hotel::class)->findOneBy([]);
        $tariffs = $dm->getRepository(Tariff::class)
            ->findBy(
                [
                    'fullTitle' => ['$in' => ['UpTariff', 'DownTariff']],
                    'hotel.id' => $hotel->getId()
                ]);
        $isUseCategory = $this->getContainer()->get('mbh.hotel.room_type_manager')->getIsUseCategories();
        $roomTypeType = $isUseCategory ? RoomTypeCategory::class : RoomType::class;
        $roomTypes = $dm->getRepository($roomTypeType)->findBy([
            'hotel.id' => $hotel->getId()
        ]);
        $tariffIds = Helper::toIds($tariffs);
        $roomTypeIds = Helper::toIds($roomTypes);
        $data = [
            'begin' => new \DateTime('midnight +8 days'),
            'end' => new \DateTime('midnight +12 days'),
            'tariffIds' => $tariffIds,
            'roomTypeIds' => $roomTypeIds,
            'type' => InvalidateQuery::PRICE_GENERATOR
        ];
        $creator = $this->getContainer()->get('mbh_search.query_creator_factory')->create($data);
        $invalidateQuery = $creator->createInvalidateQuery($data);
        $this->getContainer()->get('mbh_search.search_cache_invalidator')->invalidateByQuery($invalidateQuery);
    }


    /**
     * @param InvalidateQuery $invalidateQuery
     * @param int $expectedNumToInvalidate
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException
     */
    private function invalidate(InvalidateQuery $invalidateQuery, int $expectedNumToInvalidate): void
    {
        $invalidator = $this->getContainer()->get('mbh_search.search_cache_invalidator');
        $invalidator->flushCache();

        $results = $this->cacheWarmUp();
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');
        $cachedKeys = $redis->keys('*');
        /** @var PriceCache $priceCache */
        $this->assertCount(\count($results), $cachedKeys);

        $factory = $this->getContainer()->get('mbh_search.invalidate_adapter_factory');
        $adapter = $factory->createMessage($invalidateQuery);
        $invalidator->invalidate($adapter);
        $afterInvalidateKeys = $redis->keys('*');

        $invalidatedKeys = array_diff($cachedKeys, $afterInvalidateKeys);

        $this->assertCount($expectedNumToInvalidate, $invalidatedKeys);


        $cacheResultItem = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->createQueryBuilder(
            SearchResultCacheItem::class
        )
            ->field('cacheResultKey')->in(
                [$invalidatedKeys]
            )->getQuery()->execute()->toArray();

        $this->assertEmpty($cacheResultItem);
    }

    public function priceCacheInvalidateProvider(): array
    {
        return [
            [
                [
                    'offset' => 8,
                    'expected' => [
                        'keysNumToInvalidate' => 4,
                    ],
                ],

            ],
            [
                [
                    'offset' => 12,
                    'expected' => [
                        'keysNumToInvalidate' => 2,
                    ],
                ],

            ],
            [
                [
                    'offset' => 15,
                    'expected' => [
                        'keysNumToInvalidate' => 0,
                    ],
                ],

            ],
        ];
    }

    public function restrictionInvalidateProvider(): array
    {
        return [
            [
                [
                    'offset' => 3,
                    'expected' => [
                        'keysNumToInvalidate' => 1,
                    ],
                ],

            ],
            [
                [
                    'offset' => 5,
                    'expected' => [
                        'keysNumToInvalidate' => 2,
                    ],
                ],

            ],
            [
                [
                    'offset' => 10,
                    'expected' => [
                        'keysNumToInvalidate' => 1,
                    ],
                ],

            ],
        ];
    }

    public function roomTypeInvalidateProvider(): array
    {
        return [
            [
                [
                    'offset' => 3,
                    'expected' => [
                        'keysNumToInvalidate' => 15,
                    ],
                ],

            ],
            [
                [
                    'offset' => 5,
                    'expected' => [
                        'keysNumToInvalidate' => 15,
                    ],
                ],

            ],
            [
                [
                    'offset' => 10,
                    'expected' => [
                        'keysNumToInvalidate' => 15,
                    ],
                ],

            ],
        ];
    }

    public function tariffInvalidateProvider(): array
    {
        return [
            [
                [
                    'offset' => 3,
                    'expected' => [
                        'keysNumToInvalidate' => 21 * 2,
                    ],
                ],

            ],
            [
                [
                    'offset' => 5,
                    'expected' => [
                        'keysNumToInvalidate' => 21 * 2,
                    ],
                ],

            ],
            [
                [
                    'offset' => 10,
                    'expected' => [
                        'keysNumToInvalidate' => 21 * 2,
                    ],
                ],

            ],
        ];
    }

    public function roomCacheInvalidateProvider(): array
    {
        return [
            [
                [
                    'offset' => 3,
                    'expected' => [
                        'keysNumToInvalidate' => 5,
                    ],
                ],

            ],
            [
                [
                    'offset' => 5,
                    'expected' => [
                        'keysNumToInvalidate' => 10,
                    ],
                ],

            ],
            [
                [
                    'offset' => 10,
                    'expected' => [
                        'keysNumToInvalidate' => 5,
                    ],
                ],

            ],
        ];
    }

    private function cacheWarmUp(): array
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $hotels = $dm->getRepository(Hotel::class)->findAll();
        foreach ($hotels as $hotel) {
            /** @var Hotel $hotel */
            $hotel->setIsSearchActive(true);
        }
        $dm->flush();
        $dm->clear();

        $search = $this->getContainer()->get('mbh_search.search');
        $results = [];
        foreach (self::OFFSETS as $offset) {
            [$beginOffset, $endOffset] = $offset;
            $begin = new  \DateTime("midnight + ${beginOffset} days");
            $end = new  \DateTime("midnight + ${endOffset} days");
            $conditionsBlank = [
                'begin' => $begin->format('d.m.Y'),
                'end' => $end->format('d.m.Y'),
                'adults' => 2,
                'children' => 0,
                'isUseCache' => true,
                'errorLevel' => ErrorResultFilter::ALL
            ];
            $results[] = $search->searchSync($conditionsBlank);
        }

           return array_merge(...array_values(array_merge_recursive(...$results)));
    }
}