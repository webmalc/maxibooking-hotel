<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache\Invalidate;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateInterface;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultDayPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class SearchCacheInvalidatorTest extends WebTestCase
{
    public function testInvalidate()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $hotels = $dm->getRepository(Hotel::class)->findAll();
        foreach ($hotels as $hotel) {
            /** @var Hotel $hotel */
            $hotel->setIsSearchActive(true);
        }
        $dm->flush();
        $dm->clear();

        $invalidator = $this->getContainer()->get('mbh_search.search_cache_invalidator');
        $invalidator->flushCache();

        $startDate = new \DateTime('midnight');
        $begin = (clone $startDate)->modify('+ 0 days');
        $end = (clone $startDate)->modify('+ 6 days');
        $data = [
            'begin' =>$begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'adults' => 2,
            'children' => 0,
            'isUseCache' => true,
        ];

        $searcher = $this->getContainer()->get('mbh_search.search');
        $result = $searcher->searchSync($data);


        /** @var InvalidateInterface $data */
//        $invalidator->invalidate($data['data']);

        $redis = $this->getContainer()->get('snc_redis.cache_results_client');
        $actual = $redis->keys('*');
        $expected = $data['keysLeft'];

        $this->assertCount($expected, $actual);

    }

    public function dataProvider()
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var Hotel $hotel */
        $hotel = $dm->getRepository(Hotel::class)->findAll()[0];
        /** @var Tariff $tariff */
        $tariff = $hotel->getTariffs()->first();
        /** @var RoomType $roomType */
        $roomType = $hotel->getRoomTypes()->first();

        $priceCache1 = $this->createMock(PriceCache::class);
        $priceCache1->expects($this->exactly(2))->method('getDate')->willReturn($startDate);
//        $priceCache1->expects($this->once())->method('getTariffIds')->willReturn(['tariffId']);
//        $priceCache1->expects($this->once())->method('getRoomTypeIds')->willReturn(['roomTypeIds1', 'roomTypeIds2']);

        $priceCache1->setDate($startDate);
        $priceCache2 = new PriceCache();
        $tariff = new Tariff();
        $roomCache1 = new RoomCache();
        $roomCache2 = new RoomCache();
        $dates = [
            [
                (clone $startDate)->modify('+ 0 days'),
                (clone $startDate)->modify('+ 6 days'),

            ],
            [
                (clone $startDate)->modify('+ 4 days'),
                (clone $startDate)->modify('+ 10 days'),

            ],
            [
                (clone $startDate)->modify('+ 8 days'),
                (clone $startDate)->modify('+ 13 days'),
            ],
        ];

        return [
            [
                [
                    'data' => $priceCache1,
                    'keysLeft' => 0,
                    'dates' => $dates,
                ],
                [
                    'data' => $priceCache2,
                    'keysLeft' => 1,
                ],
            ],

        ];
    }

    private function createResults(\DateTime $begin, \DateTime $end): array
    {


        $resultRoomType = ResultRoomType::createInstance($roomType);
        $resultTariff = ResultTariff::createInstance($tariff);

        $adults = 2;
        $children = 2;
        $childrenAges = [3, 7];

        $conditions = new SearchConditions();
        $conditions
            ->setId('fakeConditionsId')
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges)
            ->setSearchHash('fakeSearchHash');

        $dayPrice = ResultDayPrice::createInstance($begin, $adults, $children, 0, 333, $resultTariff);
        $resultPrice = ResultPrice::createInstance($adults, $children, 33333, [$dayPrice]);
        $resultConditions = ResultConditions::createInstance($conditions);

        $result = Result::createInstance(
            $begin,
            $end,
            $resultConditions,
            $resultTariff,
            $resultRoomType,
            [$resultPrice],
            5,
            []
        );
        $result->setCacheItemId('fakeTestId');

        $searchQuery = new SearchQuery();
        $searchQuery
            ->setBegin($begin)
            ->setEnd($end)
            ->setTariffId($tariff->getId())
            ->setRoomTypeId($roomType->getId())
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges)
            ->setSearchConditions($conditions);

        return [
            'result' => $result,
            'query' => $searchQuery,
        ];
    }
}