<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearcherTest extends SearchWebTestCase
{
    /** @dataProvider dataProvider */
    public function testSearch($data)
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);

        $searcher = $this->getContainer()->get('mbh_search.searcher');
        $errors = [];
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
        /** @var Result $actualSearchResult */
        foreach ($actual as $actualSearchResult) {
            $this->assertInstanceOf(Result::class, $actualSearchResult);
        }

        $actualResults = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $this->assertCount($expected['okResult'], $actualResults);

        $actualErrors = array_filter($actual, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount($expected['errorResult'], $actualErrors);


        $this->assertEquals($expected['totalPrice'], reset($actualResults)->getPrices()[0]->getTotal());
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
                    'resultsCount' => 5,
                    'totalPrice' => 4400,
                    'okResult' => 1,
                    'errorResult' => 4

                ]

            ]
        ];
    }




}