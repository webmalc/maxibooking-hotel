<?php


namespace Tests\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Lib\Exceptions\ConsumerSearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\RabbitMQ\AsyncSearchConsumer;
use MBH\Bundle\SearchBundle\Services\Search\ConsumerSearcher;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearchConsumerTest extends SearchWebTestCase
{
    public function testExecuteFoundNoCache(): void
    {
        $conditionId = 'someId';
        $body = [
            'conditionsId' => $conditionId,
            'searchQueries' => serialize([new SearchQuery(), new SearchQuery()])
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $consumerSearch = $this->createMock(ConsumerSearcher::class);
        $consumerSearch->expects($this->once())->method('search')->willReturnCallback(function (string  $actualConditionId, array $searchQueries) use ($conditionId){
            $this->assertEquals($conditionId, $actualConditionId);
            foreach ($searchQueries as $searchQuery) {
                $this->assertInstanceOf(SearchQuery::class, $searchQuery);
            }
        });

        $consumer = new AsyncSearchConsumer($consumerSearch);
        $consumer->execute($message);
    }


}