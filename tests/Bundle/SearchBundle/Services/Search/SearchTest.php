<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchTest extends SearchWebTestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function testSearchSync(iterable $data): void
    {
        $search = $this->getContainer()->get('mbh_search.search');
        $conditionData = $this->createConditionData($data);
        $actual = $search->searchSync($conditionData);
        $this->assertCount($data['expected']['successResults'], $actual);
        foreach ($actual as $result) {
            $this->assertInternalType('array', $result);
        }
    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function testSearchSyncFilter(iterable $data): void
    {
        $search = $this->getContainer()->get('mbh_search.search');
        $conditionData = $this->createConditionData($data);
        $conditionData['errorLevel'] = ErrorResultFilter::ALL;
        $actual = $search->searchSync($conditionData);
        $this->assertCount($data['expected']['searchCount'], $actual);
        foreach ($actual as $result) {
            $this->assertInternalType('array', $result);
        }
    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function testGroupedSerializedSearchSync(iterable $data): void
    {
        $search = $this->getContainer()->get('mbh_search.search');
        $conditionData = $this->createConditionData($data);
        $actual = $search->searchSync($conditionData, 'roomType', true);
        $this->assertJson($actual);
    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function testSearchAsync(iterable $data): void
    {
        $expected = $data['expected'];
        $producer = $this->createMock(Producer::class);
        $producer->expects($this->exactly(1))->method('publish')->willReturnCallback(function (string $message) {
            $msg = json_decode($message, true);
            $this->assertNotEmpty($msg['conditionsId']);
            $this->assertContainsOnlyInstancesOf(SearchQuery::class, unserialize($msg['searchQueries']));
        });
        $this->getContainer()->set('old_sound_rabbit_mq.async_search_producer', $producer);
        $conditionData = $this->createConditionData($data);
        $search = $this->getContainer()->get('mbh_search.search');
        $actual = $search->searchAsync($conditionData);
        $this->dm->clear();
        $conditions = $this->dm->find(SearchConditions::class, $actual);
        $this->assertInstanceOf(SearchConditions::class, $conditions);
        $this->assertNotNull($conditions->getSearchHash());
        $this->assertSame($expected['searchCount'], $conditions->getExpectedResultsCount());
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
                    'searchCount' => 10,
                    'results' => 10-8,
                    'successResults' => 2,
                    'chunk' => 1
                ]
            ]
        ];
    }


}