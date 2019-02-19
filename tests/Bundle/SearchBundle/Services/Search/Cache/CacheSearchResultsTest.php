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
use MBH\Bundle\SearchBundle\Services\Cache\CacheSearchResults;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class CacheSearchResultsTest extends SearchWebTestCase
{

    /** @dataProvider dataProvider */
    public function testSearchInCache($data)
    {
        /** @var Result $result */
        $result = $data['result'];
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $cache = $this->getContainer()->get('snc_redis.cache_results_client');
        $cache->flushall();

        $json = $serializer->serialize($result);
        $searchQuery = $data['searchQuery'];
        $key = $this->getContainer()->get('mbh_search.cache_key_creator')->createKey($searchQuery);
        $cache->set($key, $json);

        $service = $this->getContainer()->get('mbh_search.cache_search');
        /** @var Result $actual */
        foreach ([true, false] as $hydrated) {
            $actual = $service->searchInCache($searchQuery, $hydrated);
            if ($hydrated) {
                $this->assertInstanceOf(Result::class, $actual);
                $this->assertEquals($serializer->serialize($result), $serializer->serialize($actual));
            } else {
                $this->assertInternalType('array', $actual);
                $this->assertArraySimilar($serializer->normalize($result), $actual);
            }
        }

    }

    /**
     * @dataProvider dataProvider
     */
    public function testMockedSaveToCahe($data)
    {
        $redisMock = $this->createMock(Client::class);
        $redisMock->expects($this->once())->method('__call')->willReturnCallback(function ($name, $args) use (&$createdKey, &$resultJson) {
            $this->assertEquals('set', $name);
            $this->assertEquals($args[0], $createdKey);
            $this->assertJson($args[1]);
            $this->assertEquals($args[1], $resultJson);
        });

        $dmMock = $this->createMock(DocumentManager::class);
        $repositoryMock = $this->createMock(SearchResultCacheItemRepository::class);

        $dmMock->expects($this->once())->method('persist')->willReturnCallback(function ($actual) use (&$result, &$createdKey){
            /** @var SearchResultCacheItem $actual */
            $this->assertInstanceOf(SearchResultCacheItem::class, $actual);
            $this->assertEquals($createdKey, $actual->getCacheResultKey());
        });

        $dmMock->expects($this->once())->method('flush')->willReturnCallback(function ($actual) {
            $this->assertInstanceOf(SearchResultCacheItem::class, $actual);
            $actual->setId('fakeTestId');
        });

        $dmMock->expects($this->once())->method('getRepository')->willReturn($repositoryMock);


        $repositoryMock->expects($this->once())->method('getDocumentManager')->willReturn($dmMock);
        $repositoryMock->expects($this->once())->method('fetchIdByCacheKey')->willReturn(null);

        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $keyCreator = $this->getContainer()->get('mbh_search.cache_key_creator');
        $filter = $this->getContainer()->get('mbh_search.error_result_filter');

        $logger = $this->createMock(LoggerInterface::class);


        $service = new CacheSearchResults($repositoryMock, $serializer, $redisMock, $keyCreator, $filter, $logger);

        $result = $data['result'];
        /** @noinspection PhpUnusedLocalVariableInspection */
        $resultJson = $serializer->serialize($result);
        $searchQuery = $data['searchQuery'];
        /** @noinspection PhpUnusedLocalVariableInspection */
        $createdKey = $keyCreator->createKey($searchQuery);

        $service->saveToCache($result, $searchQuery);

    }


    public function dataProvider(): iterable
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        /** @var Hotel $hotel */
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
            ->setChildrenAges($childrenAges)
            ->setSearchHash('fakeSearchHash')
        ;

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
            ->setSearchConditions($conditions)

        ;

        yield [
            [
                'result' => $result,
                'searchQuery' => $searchQuery
            ]
        ];
    }


}