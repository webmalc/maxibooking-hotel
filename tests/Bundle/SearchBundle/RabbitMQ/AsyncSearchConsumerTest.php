<?php


namespace Tests\Bundle\SearchBundle\RabbitMQ;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncSearchConsumerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\RabbitMQ\AsyncSearchConsumer;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearchConsumerTest extends SearchWebTestCase
{
    public function testExecuteFound(): void
    {
        $body = [
            'conditionsId' => 'someId',
            'searchQueries' => serialize([new SearchQuery(), new SearchQuery()])
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $searchConditions = $this->createMock(SearchConditions::class);

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($searchResult) {
            $this->assertInstanceOf(SearchResult::class, $searchResult);
        } );
        $dm->expects($this->exactly(2))->method('flush')->willReturnCallback(function ($searchResult) {
            $this->assertInstanceOf(SearchResult::class, $searchResult);
        } );

        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->once())->method('find')->willReturn($searchConditions);
        $conditionsRepository->expects($this->once())->method('getDocumentManager')->willReturn($dm);

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->exactly(2))->method('search')->willReturn(new SearchResult());

        $consumer = new AsyncSearchConsumer($searcher, $conditionsRepository);
        $consumer->execute($message);
    }

    public function testExecuteNotFound(): void
    {
        $body = [
            'conditionsId' => 'someId',
            'searchQueries' => serialize([new SearchQuery(), new SearchQuery()])
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $searchConditions = $this->createMock(SearchConditions::class);
        $searchConditions->expects($this->any())->method('getId')->willReturn($body['conditionsId']);

        $dm = $this->createMock(DocumentManager::class);
        $exceptionMessage = 'Not found message';
        $dm->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($searchResult) use ($exceptionMessage, $body) {
            /** @var SearchResult $searchResult */
            $this->assertInstanceOf(SearchResult::class, $searchResult);
            $this->assertEquals('error', $searchResult->getStatus());
            $this->assertEquals($exceptionMessage, $searchResult->getError());
            $this->assertEquals($body['conditionsId'],$searchResult->getQueryId());
        } );
        $dm->expects($this->exactly(2))->method('flush')->willReturnCallback(function ($searchResult) {
            $this->assertInstanceOf(SearchResult::class, $searchResult);
        } );
        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->once())->method('find')->willReturn($searchConditions);
        $conditionsRepository->expects($this->once())->method('getDocumentManager')->willReturn($dm);

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->exactly(2))->method('search')->willThrowException(new SearchException($exceptionMessage));

        $consumer = new AsyncSearchConsumer($searcher, $conditionsRepository);
        $consumer->execute($message);
    }

    public function testExecuteException(): void
    {
        $body = [
            'conditionsId' => 'someId',
            'searchQueries' => serialize([new SearchQuery(), new SearchQuery()])
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->once())->method('find')->willReturn(null);
        $searcher = $this->createMock(Searcher::class);
        $consumer = new AsyncSearchConsumer($searcher, $conditionsRepository);
        $this->expectException(AsyncSearchConsumerException::class);
        $consumer->execute($message);
    }

}