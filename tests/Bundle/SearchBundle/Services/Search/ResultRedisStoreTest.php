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

        $actual1 = unserialize($cache->get($key1));
        $actual2 = unserialize($cache->get($key2));

        $this->assertInstanceOf(Result::class, $actual1);
        $this->assertInstanceOf(Result::class, $actual2);
    }

    public function testReceive(): void {
        $cache = $this->getContainer()->get('snc_redis.results');
        $service = $this->getContainer()->get('mbh_search.redis_store');

        $hash = uniqid('', false);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error');
        $searchResult3 = $this->getData($hash, 'ok');
        $conditions = $searchResult1->getResultConditions()->getConditions();
        $resultsCount = 3;
        $conditions->setExpectedResultsCount($resultsCount);

        $cache->flushall();
        $service->store($searchResult1);
        $service->store($searchResult3);
        $actualResult1 = $service->receive($conditions);
        $service->store($searchResult2);
        $actualResult2 = $service->receive($conditions);

        $this->assertCount(2, $actualResult1);
        $this->assertCount(1, $actualResult2);

        $actual1 = reset($actualResult1);
        $actual3 = reset($actualResult1);
        $actual2 = reset($actualResult2);

        foreach ([$actual1, $actual2, $actual3] as $actual) {
            $this->assertInstanceOf(Result::class, $actual);
            /** @var Result $actual */
            $this->assertEquals($hash, $actual->getResultConditions()->getSearchHash());

        }
        $this->assertCount(0, $cache->keys($hash . '*'));
        $this->assertEquals($resultsCount, (int)$cache->get('received'. $hash));
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
        $resultRoomType = new ResultRoomType();
        $resultRoomType->setRoomType($roomType);
        $resultTariff = new ResultTariff();
        $resultTariff->setTariff($tariff);


        $result = new Result();
        $resultConditions = new ResultConditions();
        $resultConditions->setConditions($conditions);
        $result->setResultConditions($resultConditions);
        $result
            ->setBegin(new \DateTime())
            ->setEnd(new \DateTime())
            ->setResultRoomType($resultRoomType)
            ->setResultTariff($resultTariff)
            ->setStatus($status)
            ->setPrices([])
            ->setMinRoomsCount(0)
        ;

        return $result;
    }
}