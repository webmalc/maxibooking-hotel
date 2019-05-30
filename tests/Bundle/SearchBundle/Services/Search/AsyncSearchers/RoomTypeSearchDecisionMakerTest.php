<?php

namespace Tests\Bundle\SearchBundle\Services\Search\AsyncSearchers;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;

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
        $key = $this->getKey($searchConditions->getSearchHash(), $group->getRoomTypeId());


        $redis->del([$key]);
        $this->assertTrue($decision->isNeedSearch($searchConditions, $group));


        $redis->incr($key);
        $redis->incr($key);
        $this->assertFalse($decision->isNeedSearch($searchConditions, $group));

        $searchConditions->setAdditionalResultsLimit(3);
        $this->assertTrue($decision->isNeedSearch($searchConditions, $group));

        $redis->incr($key);
        $this->assertFalse($decision->isNeedSearch($searchConditions, $group));

    }

    public function testMarkFoundedResults()
    {
        $conditions = new SearchConditions();
        $conditions->setSearchHash('fakeTestHash1');

        $group = new QueryGroupByRoomType();
        $group->setRoomTypeId('fakeTestRoomTypeId');
        $key = $this->getKey($conditions->getSearchHash(), $group->getRoomTypeId());

        $decision = $this->getContainer()->get('mbh_search.room_type_search_decision_maker');
        $redis = $this->getContainer()->get('snc_redis.cache_results_client');

        $redis->del([$key]);

        $decision->markFoundedResults($conditions, $group);
        $decision->markFoundedResults($conditions, $group);

        $this->assertEquals(2, (int)$redis->get($key));

        $redis->del([$key]);
    }

    private function getKey(string $hash, string $roomTypeId): string
    {
        return 'already_received_room_types_' .$roomTypeId.'_'. $hash;
    }
}
