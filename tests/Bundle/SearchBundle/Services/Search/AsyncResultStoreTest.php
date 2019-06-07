<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncResultStoreTest extends SearchWebTestCase
{
    public function testStore(): void
    {
        $cache = $this->getContainer()->get('snc_redis.results');
        $cache->flushall();
        $hash = uniqid('', false);
        $conditions = new SearchConditions();
        $conditions->setSearchHash($hash);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error', ErrorResultFilter::WINDOWS);

        $service = $this->getContainer()->get('mbh_search.async_result_store');
        $service->store($searchResult1,  $conditions);
        $service->store($searchResult2,  $conditions);

        $key1 = $searchResult1->getId();
        $key2 = $searchResult2->getId();
        $this->assertTrue((bool)$cache->exists($key1));
        $this->assertTrue((bool)$cache->exists($key2));

        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $actual1 = $serializer->deserialize($cache->get($key1));
        $actual2 = $serializer->deserialize($cache->get($key2));

        $this->assertInstanceOf(Result::class, $actual1);
        $this->assertInstanceOf(Result::class, $actual2);
    }

    public function testPureReceive(): void {
        $asyncCache = $this->getContainer()->get('snc_redis.results');

        $service = $this->getContainer()->get('mbh_search.async_result_store');

        $hash = uniqid('', false);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error', ErrorResultFilter::WINDOWS);
        $searchResult3 = $this->getData($hash, 'ok');
        $conditions = new SearchConditions();
        $resultsCount = 3;
        $resultConditions = $searchResult1->getResultConditions();
        $conditions
            ->setSearchHash($resultConditions->getSearchHash())
            ->setExpectedResultsCount($resultsCount)
            ->setErrorLevel(ErrorResultFilter::WINDOWS)
        ;

        $asyncCache->flushall();
        $service->store($searchResult1, $conditions);
        $service->store($searchResult3, $conditions);
        $actualResult1 = $service->receive($conditions);
        $actualResult1 = array_merge(...array_values($actualResult1));
        $service->store($searchResult2, $conditions);
        $actualResult2 = $service->receive($conditions);
        $actualResult2 = array_merge(...array_values($actualResult2));


        $this->assertCount(2, $actualResult1);
        $this->assertCount(1, $actualResult2);

        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        $actual3 = $serializer->denormalize(array_shift($actualResult1));
        $actual1 = $serializer->denormalize(array_shift($actualResult1));
        $actual2 = $serializer->denormalize(array_shift($actualResult2));


        foreach ([$actual1, $actual2, $actual3] as $actual) {

            $this->assertInstanceOf(Result::class, $actual);
            /** @var Result $actual */
            $this->assertEquals($hash, $actual->getResultConditions()->getSearchHash());

        }
//        foreach ([
//                     [$searchResult1, $actual1],
//                     [$searchResult2, $actual2],
//                     [$searchResult3, $actual3]
//
//                 ] as $value) {
//            $this->assertEquals($serializer->serialize($value[0]), $serializer->serialize($value[1]));
//        }
        $this->assertCount(0, $asyncCache->keys($hash . '*'));
        $this->assertEquals($resultsCount, (int)$asyncCache->get('received'. $hash));
    }


    public function testKeyExpire(): void
    {
        $cache = $this->getContainer()->get('snc_redis.results');
        $service = $this->getContainer()->get('mbh_search.async_result_store');
        $result = [
            'id' => 'fakeResultExpireId',
            'result' => 'resultvalue'
        ];

        $conditions = new SearchConditions();
        $conditions->setSearchHash('fakeHash');
        $service->store($result, $conditions);

        $key = $result['id'];
        $ttl = $cache->ttl($key);
        $this->assertEquals(AsyncResultStore::EXPIRE_TIME, $ttl);

        $hash = $conditions->getSearchHash();

        $service->addFakeReceivedCount($hash, 1);

        $key = 'received_fake' . $hash;
        $ttl = $cache->ttl($key);
        $this->assertEquals(AsyncResultStore::EXPIRE_TIME, $ttl);
    }


    private function getData(string $hash, string $status, ?int $errorType = null): Result
    {
        $roomType =  $this->dm->getRepository(RoomType::class)->findOneBy([]);
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([]);
        $conditions = new SearchConditions();
        $conditions->setId('fakeId');
        $errorLevel = ErrorResultFilter::ALL;
        $conditions
            ->setSearchHash($hash)
            ->setBegin(new \DateTime())
            ->setEnd(new \DateTime())
            ->setAdults(2)
            ->setErrorLevel($errorLevel)
        ;
        $resultRoomType = ResultRoomType::createInstance($roomType);
        $resultTariff = ResultTariff::createInstance($tariff);
        $resultConditions = ResultConditions::createInstance($conditions);
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +3 days');
        $result = Result::createInstance(
            $begin,
            $end,
            $resultConditions,
            $resultTariff,
            $resultRoomType,
            [],
            0,
            []
        );
        $result->setStatus($status);
        if (null !== $errorType) {
            $result->setErrorType($errorType);
        }

        return $result;
    }
}