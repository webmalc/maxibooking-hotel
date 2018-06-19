<?php


namespace Tests\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Document\SearchResultRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncResultReceiverException;
use MBH\Bundle\SearchBundle\Services\AsyncResultReceiver;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncResultReceiverTest extends SearchWebTestCase
{
    public function testReceive()
    {
        $firstPartOk = [uniqid('', false), uniqid('', false), uniqid('', false)];
        $firstPartError = [uniqid('', false), uniqid('', false)];
        $secondPartOk = [uniqid('', false), uniqid('', false)];
        $secondPartError = [uniqid('', false), uniqid('', false), uniqid('',false)];

        $data = [
            0 => [
                'ok' => $firstPartOk,
                'error' => $firstPartError,
                'expected' => $firstPartOk
            ],
            1 => [
                'ok' => array_merge($firstPartOk, $secondPartOk),
                'error' => array_merge($firstPartError, $secondPartError),
                'expected' => $secondPartOk
            ],
            2 => [
                'ok' => array_merge($firstPartOk, $secondPartOk),
                'error' => array_merge($firstPartError, $secondPartError),
                'expected' => []
            ]

        ];


        $firstTaken = array_merge($data[0]['ok'], $data[0]['error']);
        $searchHolder = new SearchResultHolder();
        $searchHolder->setTakenSearchResultIds($firstTaken);

        $secondTaken = array_merge($data[1]['ok'], $data[1]['error']);
        $lastHolder = new SearchResultHolder();
        $lastHolder->setTakenSearchResultIds($secondTaken);

        $conditionsId = 'conditionsId';
        $conditions = new SearchConditions();
        $conditions->setId($conditionsId);
        $conditions->setExpectedResultsCount(10);

        $searchResultHolderRepository = $this->createMock(SearchResultHolderRepository::class);
        $searchResultHolderRepository->expects($this->any())->method('findOneBy')->willReturn(null, $searchHolder, $lastHolder);

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects($this->any())->method('persist')->willReturnCallback(function ($holder) use (&$taken){
            /** @var SearchResultHolder $holder */
            $this->assertNotEmpty($holder->getTakenSearchResultIds());
            $this->assertArraySimilar($taken, $holder->getTakenSearchResultIds());
        });

        $searchResultRepository = $this->createMock(SearchResultRepository::class);
        $searchResultRepository->expects($this->any())->method('fetchOkResultIds')->willReturn($data[0]['ok'], $data[1]['ok'], $data[2]['ok']);
        $searchResultRepository->expects($this->any())->method('fetchErrorResultIds')->willReturn($data[0]['error'], $data[1]['error'], $data[2]['error']);
        $searchResultRepository->expects($this->any())->method('getDocumentManager')->willReturn($dm);
        $searchResultRepository->expects($this->any())->method('fetchResultsByIds')->willReturn($firstPartOk, $secondPartOk, $secondPartOk);


        $service = new AsyncResultReceiver($searchResultRepository, $searchResultHolderRepository);

        foreach (range(0, \count($data)) as $index) {
            $value = $data[$index];
            if (empty($value['expected'])) {
                $this->expectException(AsyncResultReceiverException::class);
            }
            /** @var array $taken */
            $taken = array_merge($value['ok'], $value['error']);
            $actual = $service->receive($conditions);
            $this->assertCount(\count($value['expected']), $actual);
        }



    }
}