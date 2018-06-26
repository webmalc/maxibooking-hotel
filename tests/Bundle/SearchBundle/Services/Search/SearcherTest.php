<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearcherTest extends SearchWebTestCase
{
    /** @dataProvider dataProvider */
    public function testSearch($data)
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);

        $searcher = $this->getContainer()->get('mbh_search.searcher');

        foreach ($searchQueries as $searchQuery) {
            try {
                $actual[] = $searcher->search($searchQuery);
            } catch (SearchException $e) {
                $errors['searchError'][] = $e->getMessage();
            }
        }
        $expected = $data['expected'];
        /** @noinspection PhpUndefinedVariableInspection */
        $this->assertCount($expected['resultsCount'], $actual);
        /** @var SearchResult $actualSearchResult */
        $actualSearchResult = reset($actual);
        $this->assertEquals($expected['prices']['1_1'], $actualSearchResult->getPrices()['1_1']);
    }

    public function dataProvider(): iterable
    {
        yield [
            [
                'conditions' => [
                    'beginOffset' => 2,
                    'endOffset' => 4,
                    'tariffFullTitle' => '',
                    'roomTypeFullTitle' => 'Люкс',
                    'hotelFullTitle' => 'Отель Волга',
                    'adults' => 1,
                    'children' => 1,
                    'childrenAges' => [5],
                ],
                'expected' => [
                    'resultsCount' => 1,
                    'prices' => ['1_1' => 4400 ]

                ]

            ]
        ];
    }




}