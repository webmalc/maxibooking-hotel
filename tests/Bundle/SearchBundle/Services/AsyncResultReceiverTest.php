<?php


namespace Tests\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Document\SearchResultRepository;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncResultReceiverTest extends SearchWebTestCase
{
    public function testReceive()
    {
        $searchResultRepository = $this->createMock(SearchResultRepository::class);
        $searchResultHolderRepository = $this->createMock(SearchResultHolderRepository::class);
        $dm = $this->createMock(DocumentManager::class);
        $searchResultRepository->expects($this->once())->method('getDocumentManager')->willReturn($dm);
    }
}