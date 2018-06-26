<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchTest extends SearchWebTestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws MongoDBException
     * @throws DataHolderException
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function testSearchSync(iterable $data): void
    {
        $search = $this->getContainer()->get('mbh_search.search');
        $conditionData = $this->createConditionData($data);
        $actual = $search->searchSync($conditionData);
        $this->assertCount(2, $search->getRestrictionsErrors());
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $actual);

    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function testSearchAsync(iterable $data): void
    {
        $producer = $this->createMock(Producer::class);
        $producer->expects($this->exactly(4))->method('publish')->willReturnCallback(function (string $message) {
            $msg = json_decode($message, true);
            $this->assertNotEmpty($msg['conditionsId']);
            $this->assertContainsOnlyInstancesOf(SearchQuery::class, unserialize($msg['searchQueries']));
        });
        $this->getContainer()->set('old_sound_rabbit_mq.async_search_producer', $producer);
        $conditionData = $this->createConditionData($data);
        $search = $this->getContainer()->get('mbh_search.search');
        $search->setAsyncQueriesChunk(2);
        /** @var SearchResultHolder $actual */
        $actual = $search->searchAsync($conditionData);

        $this->dm->clear();
        $conditions = $this->dm->find(SearchConditions::class, $actual);
        $this->assertInstanceOf(SearchConditions::class, $conditions);
        $this->assertNotNull($conditions->getSearchHash());
        $this->assertSame(8, $conditions->getExpectedResultsCount());
    }

    public function syncDataProvider()
    {
        yield [
            [
                'beginOffset' => 3,
                'endOffset' => 7,
                'roomTypes' => ['Люкс'],
                'tariffs' => [],
                'hotels' => [],
                'adults' => 2,
                'children' => 0,
                'childrenAges' => [],
                'additionalBegin' => 0,
                'additionalEnd' => 0,
                'expected' => [
                    'results' => 10-2,
                    'successResults' => 2
                ]
            ]
        ];
    }


}