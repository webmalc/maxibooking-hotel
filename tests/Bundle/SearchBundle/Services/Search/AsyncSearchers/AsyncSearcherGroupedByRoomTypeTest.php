<?php


namespace Tests\Bundle\SearchBundle\Services\Search\AsyncSearchers;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\Fetcher\DataManager;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStore;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearchDecisionMakerInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherGroupedByRoomType;
use MBH\Bundle\SearchBundle\Services\Search\CacheSearcher;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearcherGroupedByRoomTypeTest extends SearchWebTestCase
{

    public function testSearch(): void
    {
        $dm = $this->createMock(DocumentManager::class);
        $searchConditions = (new SearchConditions())->setSearchHash('fakeSearchHash');
        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->exactly(3))->method('find')->willReturn($searchConditions, $searchConditions, null);
        $conditionsRepository->expects($this->exactly(2))->method('getDocumentManager')->willReturn($dm);


        $searchedResult1 = (new Result())->setStatus('ok');
        $searchedResult2 = ['status' => 'ok'];
        $searchedResult3 = (new Result())->setStatus('error');

        $searcher = $this->createMock(CacheSearcher::class);
        $searcher->expects($this->exactly(3))->method('search')->willReturn($searchedResult1, $searchedResult2, $searchedResult3);

        $searcherFactory = $this->createMock(SearcherFactory::class);
        $searcherFactory->expects($this->once())->method('getSearcher')->willReturn($searcher);

        $resultStore = $this->createMock(AsyncResultStore::class);
        $resultStore->expects($this->exactly(3))->method('store')->willReturnCallback(function ($searchResult) use (&$numberOfCall) {
            $numberOfCall++;
            if ($numberOfCall === 2) {
                $this->assertInternalType('array', $searchResult);
            } else {
                $this->assertInstanceOf(Result::class, $searchResult);
            }

        });

        $decisionMaker = $this->createMock(AsyncSearchDecisionMakerInterface::class);
        $decisionMaker->expects($this->exactly(2))->method('isNeedSearch')->willReturn(true, false);
        $decisionMaker->expects($this->exactly(1))->method('markFoundedResults');


        $resultStore->expects($this->exactly(1))->method('addFakeReceivedCount')->willReturnCallback(function ($actualHash, $actualCount) {
            $this->assertEquals(3, $actualCount);
            $this->assertEquals('fakeSearchHash', $actualHash);
        });


        $searchQuery = $this->createMock(SearchQuery::class);
        $group = new QueryGroupByRoomType();
        $group->setSearchQueries([clone $searchQuery, clone $searchQuery, clone $searchQuery]);

        $dataManager = $this->createMock(DataManager::class);

        $search = new AsyncSearcherGroupedByRoomType($conditionsRepository, $resultStore, $searcherFactory, $decisionMaker, $dataManager);
        $search->search('fakeConditionsId', $group);
        $search->search('fakeConditionsId', $group);

        $this->expectException(ConsumerSearchException::class);
        $search->search('fakeConditionsId', $group);

    }
}





