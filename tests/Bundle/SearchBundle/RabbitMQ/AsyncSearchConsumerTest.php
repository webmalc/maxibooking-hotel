<?php


namespace Tests\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchConditionsRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncSearchConsumerException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\RabbitMQ\AsyncSearchConsumer;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\AsyncResultStoreInterface;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use MBH\Bundle\SearchBundle\Services\Search\SearcherFactory;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearchConsumerTest extends SearchWebTestCase
{
    public function testExecuteFoundNoCache(): void
    {
        $body = [
            'conditionsId' => 'someId',
            'searchQueries' => serialize([new SearchQuery(), new SearchQuery()])
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $searchConditions = $this->createMock(SearchConditions::class);

        $conditionsRepository = $this->createMock(SearchConditionsRepository::class);
        $conditionsRepository->expects($this->once())->method('find')->willReturn($searchConditions);

        $searchedResult1 = new Result();

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->exactly(2))->method('search')->willReturn($searchedResult1);

        $searcherFactory = $this->createMock(SearcherFactory::class);
        $searcherFactory->expects($this->once())->method('getSearcher')->willReturn($searcher);

        $stocker = $this->createMock(AsyncResultStoreInterface::class);
        $stocker->expects($this->exactly(2))->method('store')->willReturnCallback(function ($searchResult) {
            $this->assertInstanceOf(Result::class, $searchResult);
        } );

        $consumer = new AsyncSearchConsumer($searcherFactory, $conditionsRepository, $stocker);
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
        $searcherFactory = $this->createMock(SearcherFactory::class);
        $searcherFactory->expects($this->never())->method('getSearcher')->willReturn($searcher);

        $resultStore = $this->createMock(AsyncResultStoreInterface::class);
        $consumer = new AsyncSearchConsumer($searcherFactory, $conditionsRepository, $resultStore);
        $this->expectException(AsyncSearchConsumerException::class);
        $consumer->execute($message);
    }

}