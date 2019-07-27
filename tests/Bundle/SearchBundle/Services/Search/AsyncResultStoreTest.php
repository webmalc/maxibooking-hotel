<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\WindowsCheckLimitException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\SearchConditionsInterface;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\DayPriceBuilder;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\DayPriceDirector;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\PriceBuilder;
use MBH\Bundle\SearchBundle\Services\Search\Result\Builder\PriceDirector;
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
        $searchResult1 = $this->getData('ok');
        $searchResult2 = $this->getData('error', new WindowsCheckLimitException('error'));

        $service = $this->getContainer()->get('mbh_search.async_result_store');
        $service->storeInStock($searchResult1,  $conditions);
        $service->storeInStock($searchResult2,  $conditions);

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
        $searchResult1 = $this->getData('ok');
        $searchResult2 = $this->getData('error', new WindowsCheckLimitException('error'));
        $searchResult3 = $this->getData('ok');

        /** @var SearchConditionsInterface|SearchConditions $conditions */
        $conditions = new SearchConditions();
        $resultsCount = 3;
        $conditions
            ->setSearchHash($hash)
            ->setExpectedResultsCount($resultsCount)
            ->setErrorLevel(ErrorResultFilter::WINDOWS)
        ;

        $asyncCache->flushall();
        $service->storeInStock($searchResult1, $conditions);
        $service->storeInStock($searchResult3, $conditions);
        $actualResult1 = $service->receiveFromStock($conditions);
        $actualResult1 = array_merge(...array_values($actualResult1));
        $service->storeInStock($searchResult2, $conditions);
        $actualResult2 = $service->receiveFromStock($conditions);
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
//            $this->assertEquals($hash, $actual->getResultConditions()->getSearchHash());

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
        $service->storeInStock($result, $conditions);

        $key = $result['id'];
        $ttl = $cache->ttl($key);
        $this->assertEquals(AsyncResultStore::EXPIRE_TIME, $ttl);

        $hash = $conditions->getSearchHash();

        $service->addFakeToStock($hash, 1);

        $key = 'received_fake' . $hash;
        $ttl = $cache->ttl($key);
        $this->assertEquals(AsyncResultStore::EXPIRE_TIME, $ttl);
    }


    private function getData(string $status, SearchException $e = null): Result
    {

        /** @var RoomType $roomType */
        $roomType =  $this->dm->getRepository(RoomType::class)->findOneBy([]);
        /** @var Tariff $tariff */
        $tariff = $this->dm->getRepository(Tariff::class)->findOneBy([]);

        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +3 days');
        $adults = 2;
        $children = 0;

        $searchQuery = new SearchQuery();
        $searchQuery->setAdults($adults);
        $searchQuery->setBegin($begin);
        $searchQuery->setEnd($end);
        $searchQuery->setTariffId($tariff->getId());
        $searchQuery->setRoomTypeId($roomType->getId());


        $tourists = [
            'mainAdults' => $adults,
            'addsAdults' => $children,
            'mainChildren' => 0,
            'addsChildren' => 0,
        ];

        $dayPriceBuilder = new DayPriceBuilder();
        $dayPriceDirector = new DayPriceDirector($dayPriceBuilder);
        $dayPrices = [];
        foreach (new \DatePeriod($begin, new \DateInterval('P1D'), (clone $end)->modify('+1 day')) as $date) {
            $dayPrices[] = $dayPriceDirector->createDayPrice($date, $tourists, $roomType, $tariff, 333.3);
        }

        $priceBuilder = new PriceBuilder();
        $priceDirector = new PriceDirector($priceBuilder);
        $prices = $priceDirector->createPrice($searchQuery, $dayPrices);

        $resultCreator = $this->getContainer()->get('mbh_search.search_result_creator');


        $result = null;
        if ($status === 'ok') {
            $result = $resultCreator->createResult($searchQuery, [$prices], 1);
        }
        if ($status === 'error') {
            $result = $resultCreator->createErrorResult($searchQuery, $e);
        }

        if (null === $result || !($result instanceof Result)) {
            throw new \Exception('Test Exception in '. __CLASS__);
        }

        return $result;
    }
}