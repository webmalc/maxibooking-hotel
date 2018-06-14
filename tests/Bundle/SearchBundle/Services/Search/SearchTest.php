<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Document\SearchResultHolder;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataHolderException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Services\Search\Search;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchTest extends SearchWebTestCase
{

    /** @var Search */
    private $search;

    public function setUp()
    {
        parent::setUp();
        $this->search = $this->getContainer()->get('mbh_search.search');
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
        $conditionData = $this->createConditionData($data);
        $actual = $this->search->searchSync($conditionData);
        $this->assertCount(2, $this->search->getRestrictionsErrors());
        $this->assertContainsOnlyInstancesOf(SearchResult::class, $actual);

    }

    /** @dataProvider syncDataProvider
     * @param iterable $data
     * @throws SearchConditionException
     * @throws SearchQueryGeneratorException
     */
    public function testSearchAsync(iterable $data): void
    {
        $conditionData = $this->createConditionData($data);
        /** @var SearchResultHolder $actual */
        $actual = $this->search->searchAsync($conditionData);
        $holder = $this->dm->find(SearchResultHolder::class, $actual);
        $this->assertInstanceOf(SearchResultHolder::class, $holder);
        $this->assertSame(8, $holder->getExpectedResultsCount());
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