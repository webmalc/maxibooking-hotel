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
            ->expects($this->exactly($data['expected']['resultsCount']))->method('searchInCache')
            ->willReturn(...$data['mockResults']);

        $searchCache->expects($this->exactly($data['expected']['resultsNull']))->method('saveToCache')->willReturnCallback(function ($expectedResult) {
            $this->assertInstanceOf(Result::class, $expectedResult);
        });

        $this->getContainer()->set('mbh_search.cache_search', $searchCache);
        $searcher = $this->getContainer()->get('mbh_search.cache_searcher');
        $actual = [];

        foreach ($searchQueries as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $actual[] = $searcher->search($searchQuery);
        }

        $this->assertCount($data['expected']['resultsCount'], $actual);
        foreach ($actual as $result) {
            $this->assertInstanceOf(Result::class, $result);
        }

        $actualResults = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $actualErrors = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount($data['expected']['okResult'], $actualResults);
        $this->assertCount($data['expected']['errorResult'], $actualErrors);
    }

    /** @dataProvider dataProvider */
    public function testSearchWithNoCache($data)
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);
        $searchCache = $this->createMock(SearchCacheInterface::class);
        $searchCache->expects($this->exactly($data['expected']['resultsCount']))->method('searchInCache')->willReturnCallback(function ($expectedSearchQuery) {
            $this->assertInstanceOf(SearchQuery::class, $expectedSearchQuery);

            return null;
        });
        $searchCache->expects($this->exactly($data['expected']['resultsCount']))->method('saveToCache')->willReturnCallback(function ($expectedResult) {
            $this->assertInstanceOf(Result::class, $expectedResult);
        });

        $this->getContainer()->set('mbh_search.cache_search', $searchCache);
        $searcher = $this->getContainer()->get('mbh_search.cache_searcher');
        $actual = [];

        foreach ($searchQueries as $searchQuery) {
            /** @var SearchQuery $searchQuery */
            $actual[] = $searcher->search($searchQuery);
        }

        $this->assertCount($data['expected']['resultsCount'], $actual);
        foreach ($actual as $result) {
            $this->assertInstanceOf(Result::class, $result);
        }

        $actualResults = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $actualErrors = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount($data['expected']['okResult'], $actualResults);
        $this->assertCount($data['expected']['errorResult'], $actualErrors);
    }

}