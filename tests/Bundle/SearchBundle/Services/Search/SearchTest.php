<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use Doctrine\ODM\MongoDB\MongoDBException;
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
        $this->search->searchSync($conditionData);
        $actualResultsCount = $this->search->getSearchCount();
        $actualHash = $this->search->getSearchHash();
        $this->assertNotEmpty($actualHash);
        $this->assertEquals($data['expected']['results'], $actualResultsCount);
        $this->assertCount(2, $this->search->getRestrictionsErrors());

    }

    /** @dataProvider syncDataProvider */
    public function testSearchAsync(iterable $data): void
    {
        $conditionData = $this->createConditionData($data);
        /** @var SearchResultHolder $actual */
        $actual = $this->search->searchAsync($conditionData);
        $this->assertEquals($this->search->getSearchCount(), $actual->getExpectedResults());
        $this->assertEquals($this->search->getSearchHash(), $actual->getHash());
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
                    'results' => 10-2
                ]
            ]
        ];
    }


}