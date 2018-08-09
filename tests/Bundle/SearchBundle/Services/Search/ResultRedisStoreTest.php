<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultRoomType;
use MBH\Bundle\SearchBundle\Lib\Result\ResultTariff;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class ResultRedisStoreTest extends SearchWebTestCase
{
    public function testStore(): void
    {
        $cache = $this->getContainer()->get('snc_redis.results');
        $cache->flushall();
        $hash = uniqid('', false);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error');

        $service = $this->getContainer()->get('mbh_search.redis_store');
        $service->store($searchResult1);
        $service->store($searchResult2);

        $key1 = "{$hash}{$searchResult1->getId()}";
        $key2 = "{$hash}{$searchResult2->getId()}";
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
        $service = $this->getContainer()->get('mbh_search.redis_store');

        $hash = uniqid('', false);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error');
        $searchResult3 = $this->getData($hash, 'ok');
        $conditions = new SearchConditions();
        $resultsCount = 3;
        $resultConditions = $searchResult1->getResultConditions();
        $conditions
            ->setSearchHash($resultConditions->getSearchHash())
            ->setExpectedResultsCount($resultsCount)
        ;

        $asyncCache->flushall();
        $service->store($searchResult1);
        $service->store($searchResult3);
        $actualResult1 = $service->receive($conditions, false, null, null);
        $service->store($searchResult2);
        $actualResult2 = $service->receive($conditions, false, null, null);

        $this->assertCount(2, $actualResult1);
        $this->assertCount(1, $actualResult2);

        $actual1 = array_pop($actualResult1);
        $actual3 = array_pop($actualResult1);
        $actual2 = array_pop($actualResult2);

        foreach ([$actual1, $actual2, $actual3] as $actual) {
            $this->assertInstanceOf(Result::class, $actual);
            /** @var Result $actual */
            $this->assertEquals($hash, $actual->getResultConditions()->getSearchHash());

        }
        $serializer = $this->getContainer()->get('mbh_search.result_serializer');
        foreach ([
                     [$searchResult1, $actual1],
                     [$searchResult2, $actual2],
                     [$searchResult3, $actual3]

                 ] as $value) {
            $this->assertEquals($serializer->serialize($value[0]), $serializer->serialize($value[1]));
        }
        $this->assertCount(0, $asyncCache->keys($hash . '*'));
        $this->assertEquals($resultsCount, (int)$asyncCache->get('received'. $hash));
    }

    private function getData(string $hash, string $status): Result
    {
        $roomType =  $this->dm->getRepository(RoomType::class)->findOneBy([]);
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([]);
        $conditions = new SearchConditions();
        $conditions->setId('fakeId');
        $conditions
            ->setSearchHash($hash)
            ->setBegin(new \DateTime())
            ->setEnd(new \DateTime())
            ->setAdults(2)
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

        return $result;
    }
}