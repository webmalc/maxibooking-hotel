<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Cache;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItem;
use MBH\Bundle\SearchBundle\Document\SearchResultCacheItemRepository;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultDayPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultPrice;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\SearchCache;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchCacheTest extends SearchWebTestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testMockedSaveToCahe($data)
    {
        $dmMock = $this->createMock(DocumentManager::class);
        $dmMock->expects($this->once())->method('persist')->willReturnCallback(function ($actual) {
            $this->assertInstanceOf(SearchResultCacheItem::class, $actual);
        });

        $dmMock->expects($this->once())->method('flush')->willReturnCallback(function ($actual) {
            $this->assertInstanceOf(SearchResultCacheItem::class, $actual);
        });

        $repositoryMock = $this->createMock(SearchResultCacheItemRepository::class);
        $repositoryMock->expects($this->once())->method('getDocumentManager')->willReturn($dmMock);

        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $service = new SearchCache($repositoryMock, $serializer);

        $result = $data['result'];
        $service->saveToCache($result);

    }

    /** @dataProvider dataProvider */
    public function testMockedSearchInCache($data)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $dm->getRepository(SearchResultCacheItem::class)->flushCache();
        /** @var Result $result */
        $result = $data['result'];
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $cacheItem = SearchResultCacheItem::createInstance($result, $serializer);


        $dm->persist($cacheItem);
        $dm->flush($cacheItem);
        $dm->clear();

        $service = $this->getContainer()->get('mbh_search.cache_search');
        /** @var Result $actual */
        $actual = $service->searchInCache($data['searchQuery']);
        $this->assertInstanceOf(Result::class, $actual);
        $this->assertEquals($serializer->serialize($result), $serializer->serialize($actual));

    }

    public function testInvalidateCache()
    {

    }

    public function dataProvider(): iterable
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $hotel = $dm->getRepository(Hotel::class)->findAll()[0];

        /** @var Tariff $tariff */
        $tariff = $hotel->getTariffs()->first();
        /** @var RoomType $roomType */
        $roomType = $hotel->getRoomTypes()->first();

        $resultRoomType = ResultRoomType::createInstance($roomType);
        $resultTariff = ResultTariff::createInstance($tariff);

        $adults = 2;
        $children = 2;
        $childrenAges = [3, 7];
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 3 days');
        $conditions = new SearchConditions();
        $conditions
            ->setId('fakeConditionsId')
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges);

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

        $searchQuery = new SearchQuery();
        $searchQuery
            ->setBegin($begin)
            ->setEnd($end)
            ->setTariffId($tariff->getId())
            ->setRoomTypeId($roomType->getId())
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges);

        yield [
            [
                'result' => $result,
                'searchQuery' => $searchQuery
            ]
        ];
    }


}