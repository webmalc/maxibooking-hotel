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
    public function testSaveToCahe($data)
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

        $service = new SearchCache($repositoryMock);

        $result = $data['result'];
        $service->saveToCache($result);

    }

    /** @dataProvider dataProvider */
    public function testSearchInCache($data)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $dm->getRepository(SearchResultCacheItem::class)->flushCache();
        /** @var Result $result */
        $result = $data['result'];
        $cacheItem = new SearchResultCacheItem();
        $cacheItem
            ->setBegin($result->getBegin())
            ->setEnd($result->getEnd())
            ->setTariffId($result->getResultTariff()->getId())
            ->setRoomTypeId($result->getResultRoomType()->getId())
            ->setAdults($result->getResultConditions()->getAdults())
            ->setChildren($result->getResultConditions()->getChildren())
            ->setChildrenAges($result->getResultConditions()->getChildrenAges())
            ->setSerializedSearchResult(json_encode($result, JSON_UNESCAPED_UNICODE))
        ;

        $dm->persist($cacheItem);
        $dm->flush($cacheItem);
        $dm->clear();

        $service = $this->getContainer()->get('mbh_search.cache_search');
        /** @var Result $actual */
        $actual = $service->searchInCache($data['searchQuery']);
        $this->assertInstanceOf(Result::class, $actual);
        $this->assertEquals($result->getResultRoomType()->getName(), $actual->getResultRoomType()->getName());
        $this->assertEquals($result->getResultTariff()->getTariffName(), $actual->getResultTariff()->getTariffName());

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

        $resultRoomType = new ResultRoomType();
        $resultRoomType->setRoomType($roomType);

        $resultTariff = new ResultTariff();
        $resultTariff->setTariff($tariff);


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


        $dayPrice = new ResultDayPrice();
        $dayPrice
            ->setAdults($adults)
            ->setChildren($children)
            ->setInfants(0)
            ->setDate($begin)
            ->setTariff($resultTariff)
            ->setPrice(333)
        ;

        $resultPrice = new ResultPrice();
        $resultPrice
            ->setSearchAdults($adults)
            ->setSearchChildren($children)
            ->setChildrenAges($childrenAges)
            ->setTotal(33333)
            ->addDayPrice($dayPrice)

        ;

        $resultConditions = new ResultConditions();
        $resultConditions->setConditions($conditions);



        $result = new Result();
        $result
            ->setBegin($begin)
            ->setEnd($end)
            ->setResultTariff($resultTariff)
            ->setResultRoomType($resultRoomType)
            ->setResultConditions($resultConditions)
            ->setPrices([$resultPrice])
            ->setMinRoomsCount(5)
        ;

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