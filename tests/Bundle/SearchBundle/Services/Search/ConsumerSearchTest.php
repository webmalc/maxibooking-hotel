<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\ResultRedisStore;
use MBH\Bundle\SearchBundle\Services\Search\ConsumerSearch;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class ConsumerSearchTest extends SearchWebTestCase
{

    public function testSearch(): void
    {
        $dm = $this->createMock(DocumentManager::class);
        $searchConditions = (new SearchConditions())->setSearchHash('fakeSearchHash');
        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->exactly(3))->method('find')->willReturn($searchConditions, $searchConditions, null);
        $conditionsRepository->expects($this->exactly(2))->method('getDocumentManager')->willReturn($dm);


        $searchedResult1 = (new Result())->setStatus('ok');
        $searchedResult2 = (new Result())->setStatus('ok');
        $searchedResult3 = (new Result())->setStatus('error');

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->exactly(3))->method('search')->willReturn($searchedResult1, $searchedResult2, $searchedResult3);

        $searcherFactory = $this->createMock(SearcherFactory::class);
        $searcherFactory->expects($this->once())->method('getSearcher')->willReturn($searcher);

        $resultStore = $this->createMock(ResultRedisStore::class);
        $resultStore->expects($this->exactly(3))->method('store')->willReturnCallback(function ($searchResult) {
            $this->assertInstanceOf(Result::class, $searchResult);
        });
        $resultStore->expects($this->exactly(2))->method('getAlreadySearchedDay')->willReturn(4, 6);
        $resultStore->expects($this->exactly(1))->method('addFakeReceivedCount')->willReturnCallback(function ($actualHash, $actualCount) {
            $this->assertEquals(3, $actualCount);
            $this->assertEquals('fakeSearchHash', $actualHash);
        });
        $resultStore->expects($this->once())->method('increaseAlreadySearchedDay');


        $searchQuery = $this->createMock(SearchQuery::class);

        $search = new ConsumerSearch($conditionsRepository, $resultStore, $searcherFactory);
        $search->search('fakeConditionsId', [clone $searchQuery, clone $searchQuery, clone $searchQuery]);
        $search->search('fakeConditionsId', [clone $searchQuery, clone $searchQuery, clone $searchQuery]);
        $this->expectException(ConsumerSearchException::class);
        $search->search('fakeConditionsId', [clone $searchQuery, clone $searchQuery, clone $searchQuery]);

    }
}






