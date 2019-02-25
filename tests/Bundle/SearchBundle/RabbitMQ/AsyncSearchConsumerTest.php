<?php


namespace Tests\Bundle\SearchBundle\RabbitMQ;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\RabbitMQ\AsyncSearchConsumer;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupByRoomType;
use MBH\Bundle\SearchBundle\Services\QueryGroups\QueryGroupInterface;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearchers\AsyncSearcherGroupedByRoomType;
use MBH\Bundle\SearchBundle\Services\Search\AsyncSearcher;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class AsyncSearchConsumerTest extends SearchWebTestCase
{
    public function testExecuteFoundNoCache(): void
    {
        $conditionId = 'someId';
        $group = new QueryGroupByRoomType();
        $group->setSearchQueries([new SearchQuery(), new SearchQuery()]);
        $body = [
            'conditionsId' => $conditionId,
            'searchQueriesGroup' => serialize($group)
        ];
        $message = $this->createMock(AMQPMessage::class);
        $message->expects($this->once())->method('getBody')->willReturn(json_encode($body));

        $consumerSearch = $this->createMock(AsyncSearcherGroupedByRoomType::class);
        $consumerSearch->expects($this->once())->method('search')->willReturnCallback(function (string  $actualConditionId, QueryGroupInterface $group) use ($conditionId){
            $this->assertEquals($conditionId, $actualConditionId);
            $searchQueries = $group->getSearchQueries();
            foreach ($searchQueries as $searchQuery) {
                $this->assertInstanceOf(SearchQuery::class, $searchQuery);
            }
        });

        $consumer = new AsyncSearchConsumer();
        $consumer->addSearcher('queryGroupByRoomType', $consumerSearch);
        $consumer->execute($message);
    }


}