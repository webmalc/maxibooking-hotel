<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultConditions;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class ResultRedisStoreTest extends SearchWebTestCase
{
    public function testStore()
    {
        $cache = $this->getContainer()->get('mbh_search.search_result_redis_cache');
        $service = $this->getContainer()->get('mbh_search.redis_store');
        $hash = uniqid('', false);
        $searchResult1 = $this->getData($hash, 'ok');
        $searchResult2 = $this->getData($hash, 'error');
        $service->store($searchResult1);
        $service->store($searchResult2);
        $actual = $cache->get($hash);
        $a = 'b';
    }

    private function getData(string $hash, string $status): Result
    {
        $conditions = new SearchConditions();
        $conditions->setSearchHash($hash);
        $result = new Result();
        $resultConditions = new ResultConditions();
        $resultConditions->setConditions($conditions);
        $result->setResultConditions($resultConditions);
        $result->setStatus($status);

        return $result;
    }
}