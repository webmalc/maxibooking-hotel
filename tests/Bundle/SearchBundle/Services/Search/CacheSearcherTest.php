<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\SearchCacheInterface;

class CacheSearcherTest extends SearcherTest
{

    /** @dataProvider dataProvider */
    public function testSearchWithCache($data)
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);
        $searchCache = $this->createMock(SearchCacheInterface::class);
        $searchCache
            ->expects($this->exactly(5))->method('searchInCache')
            ->willReturn(
                (new Result())->setStatus('ok'),
                (new Result())->setStatus('error'),
                null,
                (new Result())->setStatus('error'),
                (new Result())->setStatus('error')
            );

        $searchCache->expects($this->exactly(1))->method('saveToCache')->willReturnCallback(function ($expectedResult) {
            $this->assertInstanceOf(Result::class, $expectedResult);
        });

        $this->getContainer()->set('mbh_search.cache_search', $searchCache);
        $searcher = $this->getContainer()->get('mbh_search.cache_searcher');
        $actual = [];

        foreach ($searchQueries as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $actual[] = $searcher->search($searchQuery);
        }

        $this->assertCount(5, $actual);
        foreach ($actual as $result) {
            $this->assertInstanceOf(Result::class, $result);
        }

        $actualResults = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $actualErrors = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount(1, $actualResults);
        $this->assertCount(4, $actualErrors);
    }

    /** @dataProvider dataProvider */
    public function testSearchWithNoCache($data)
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);
        $searchCache = $this->createMock(SearchCacheInterface::class);
        $searchCache->expects($this->exactly(5))->method('searchInCache')->willReturnCallback(function ($expectedSearchQuery) {
            $this->assertInstanceOf(SearchQuery::class, $expectedSearchQuery);

            return null;
        });
        $searchCache->expects($this->exactly(5))->method('saveToCache')->willReturnCallback(function ($expectedResult) {
            $this->assertInstanceOf(Result::class, $expectedResult);
        });

        $this->getContainer()->set('mbh_search.cache_search', $searchCache);
        $searcher = $this->getContainer()->get('mbh_search.cache_searcher');
        $actual = [];

        foreach ($searchQueries as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $actual[] = $searcher->search($searchQuery);
        }

        $this->assertCount(5, $actual);
        foreach ($actual as $result) {
            $this->assertInstanceOf(Result::class, $result);
        }

        $actualResults = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $actualErrors = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount(1, $actualResults);
        $this->assertCount(4, $actualErrors);
    }

}