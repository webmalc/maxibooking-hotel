<?php

namespace Tests\Bundle\SearchBundle\Services\Search\AsyncSearchers;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\RoomTypeSearchDecisionMaker;

class RoomTypeSearchDecisionMakerTest extends WebTestCase
{

    public function testIsNeedSearch()
    {
        $searchConditions = new SearchConditions();
        $searchConditions->setSearchHash('fakeTestHash1');


        $group = new QueryGroupByRoomType();
        $group->setRoomTypeId('fakeTestRoomTypeId');

        $decision = $this->getContainer()->get('mbh_search.room_type_search_decision_maker');
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');
        $key = $this->getFoundedKey($searchConditions->getSearchHash(), $group->getRoomTypeId());


        $redis->del([$key]);
        $searchConditions->setAdditionalResultsLimit(1);
        $this->assertTrue($decision->isNeedSearch($searchConditions, $group));


        $redis->incr($key);
        $redis->incr($key);
        $this->assertFalse($decision->isNeedSearch($searchConditions, $group));

        $searchConditions->setAdditionalResultsLimit(3);
        $this->assertTrue($decision->isNeedSearch($searchConditions, $group));

        $redis->incr($key);
        $this->assertFalse($decision->isNeedSearch($searchConditions, $group));

    }

    public function testMarkFoundedResults(): void
    {
        $conditions = new SearchConditions();
        $conditions->setSearchHash('fakeTestHash1');

        $group = new QueryGroupByRoomType();
        $group->setRoomTypeId('fakeTestRoomTypeId');
        $key = $this->getFoundedKey($conditions->getSearchHash(), $group->getRoomTypeId());

        $decision = $this->getContainer()->get('mbh_search.room_type_search_decision_maker');
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');

        $redis->del([$key]);

        $decision->markFoundedResults($conditions, $group);
        $decision->markFoundedResults($conditions, $group);

        $this->assertEquals(2, (int)$redis->get($key));

        $redis->del([$key]);
    }

    public function testCanIStoreInStock(): void
    {
        $searchConditions = new SearchConditions();
        $searchConditions->setSearchHash('fakeTestHash1');

        $group = new QueryGroupByRoomType();
        $group->setRoomTypeId('fakeTestRoomTypeId');

        $decision = $this->getContainer()->get('mbh_search.room_type_search_decision_maker');
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');
        $key = $this->getStoredInStockKey($searchConditions->getSearchHash(), $group->getRoomTypeId());


        $redis->del([$key]);
        $searchConditions->setAdditionalResultsLimit(1);
        $this->assertTrue($decision->canIStoreInStock($searchConditions, $group));


        $redis->incr($key);
        $redis->incr($key);
        $this->assertFalse($decision->canIStoreInStock($searchConditions, $group));

        $searchConditions->setAdditionalResultsLimit(3);
        $this->assertTrue($decision->canIStoreInStock($searchConditions, $group));

        $redis->incr($key);
        $this->assertFalse($decision->canIStoreInStock($searchConditions, $group));

        $group->setGroupIsMain(true);
        $this->assertTrue($decision->canIStoreInStock($searchConditions, $group));

        $redis->del([$key]);
    }

    public function testMarkStoredInStockResult(): void
    {
        $conditions = new SearchConditions();
        $conditions->setSearchHash('fakeTestHash1');

        $group = new QueryGroupByRoomType();
        $group->setRoomTypeId('fakeTestRoomTypeId');
        $key = $this->getStoredInStockKey($conditions->getSearchHash(), $group->getRoomTypeId());

        $decision = $this->getContainer()->get('mbh_search.room_type_search_decision_maker');
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');

        $redis->del([$key]);

        $decision->markStoredInStockResult($conditions, $group);
        $decision->markStoredInStockResult($conditions, $group);

        $this->assertEquals(2, (int)$redis->get($key));

        $redis->del([$key]);
    }

    private function getFoundedKey(string $hash, string $roomTypeId): string
    {
        return RoomTypeSearchDecisionMaker::getFoundedKey($hash, $roomTypeId);
    }

    private function getStoredInStockKey(string $hash, string $roomTypeId): string
    {
        return RoomTypeSearchDecisionMaker::getStoredInStackKey($hash, $roomTypeId);
    }
}
