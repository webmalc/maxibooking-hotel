<?php


namespace Tests\Bundle\SearchBundle\RabbitMQ;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Document\SearchResultHolderRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncSearchConsumerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\RabbitMQ\AsyncSearchConsumer;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearchConsumerTest extends SearchWebTestCase
{
    public function testExecuteSuccess(): void
    {
        $body = [
            'holderId' => 'someId',
            'searchQuery' => serialize(new SearchQuery())
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $searchResultHolder = $this->createMock(SearchResultHolder::class);
        $searchResultHolder->expects($this->once())->method('addSearchResult')->willReturnCallback(function ($searchResult) use ($searchResultHolder){
            /** @var SearchResult $searchResult */
            $this->assertInstanceOf(SearchResult::class, $searchResult);
            $this->assertEquals('ok', $searchResult->getStatus());
            return $searchResultHolder;
        });

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects($this->once())->method('flush');
        $holderRepository = $this->createMock(SearchResultHolderRepository::class);
        $holderRepository->expects($this->once())->method('find')->willReturn($searchResultHolder);
        $holderRepository->expects($this->once())->method('getDocumentManager')->willReturn($dm);

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->once())->method('search')->willReturn(new SearchResult());

        $consumer = new AsyncSearchConsumer($searcher, $holderRepository);
        $consumer->execute($message);
    }

    public function testExecuteError(): void
    {
        $body = [
            'holderId' => 'someId',
            'searchQuery' => serialize(new SearchQuery())
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $searchResultHolder = $this->createMock(SearchResultHolder::class);
        $exceptionMessage = 'Error message';
        $searchResultHolder->expects($this->once())->method('addSearchResult')->willReturnCallback(function($searchResult) use ($exceptionMessage, $searchResultHolder) {
            /** @var SearchResult $searchResult */
            $this->assertInstanceOf(SearchResult::class, $searchResult);
            $this->assertEquals('error', $searchResult->getStatus());
            $this->assertEquals($exceptionMessage, $searchResult->getError());
            return $searchResultHolder;
        });

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects($this->once())->method('flush');
        $holderRepository = $this->createMock(SearchResultHolderRepository::class);
        $holderRepository->expects($this->once())->method('find')->willReturn($searchResultHolder);
        $holderRepository->expects($this->once())->method('getDocumentManager')->willReturn($dm);

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->once())->method('search')->willThrowException(new SearchException($exceptionMessage));

        $consumer = new AsyncSearchConsumer($searcher, $holderRepository);
        $consumer->execute($message);
    }

    public function testExecuteException(): void
    {
        $body = [
            'holderId' => 'someId',
            'searchQuery' => serialize(new SearchQuery())
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $holderRepository = $this->createMock(SearchResultHolderRepository::class);
        $holderRepository->expects($this->once())->method('find')->willReturn(null);
        $searcher = $this->createMock(Searcher::class);
        $consumer = new AsyncSearchConsumer($searcher, $holderRepository);
        $this->expectException(AsyncSearchConsumerException::class);
        $consumer->execute($message);
    }

}