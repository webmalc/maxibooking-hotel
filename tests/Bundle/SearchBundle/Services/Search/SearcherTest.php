<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearcherTest extends SearchWebTestCase
{
    /** @dataProvider dataProvider */
    public function testSearch($data): void
    {
        $searchQueries = $this->createSearchQueries($data['conditions']);

        $searcher = $this->getContainer()->get('mbh_search.searcher');
        foreach ($searchQueries as $searchQuery) {
            $actual[] = $searcher->search($searchQuery);
        }
        $expected = $data['expected'];
        /** @noinspection PhpUndefinedVariableInspection */
        $this->assertCount($expected['resultsCount'], $actual);
        /** @var Result $actualSearchResult */
        foreach ($actual as $actualSearchResult) {
            $this->assertInstanceOf(Result::class, $actualSearchResult);
        }

        $actualResults = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });

        $this->assertCount($expected['okResult'], $actualResults);

        $actualErrors = array_filter($actual, static function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'error';
        });

        $this->assertCount($expected['errorResult'], $actualErrors);
        $this->assertEquals($expected['totalPrice'], reset($actualResults)->getPrices()[0]->getTotal());
    }

    public function dataProvider(): iterable
    {
        $mockResults1 = [];
        $mockResults1 = array_merge($mockResults1, $this->resultGenerate(1, 'ok'));
        $mockResults1 = array_merge($mockResults1, $this->resultGenerate(3, 'error'));
        $mockResults1 = array_merge($mockResults1, $this->resultGenerate(1, null));


        $mockResults2 = [];
        $mockResults2 = array_merge($mockResults2, $this->resultGenerate(6, 'ok'));
        $mockResults2 = array_merge($mockResults2, $this->resultGenerate(30, 'error'));
        $mockResults2 = array_merge($mockResults2, $this->resultGenerate(4, null));



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
                    'additionalBegin' => 0,
                    'additionalEnd' => 0
                ],
                'expected' => [
                    'resultsCount' => 5,
                    'resultsNull' => 1,
                    'totalPrice' => 4400,
                    'okResult' => 1,
                    'errorResult' => 4

                ],
                'mockResults' => $mockResults1

            ]
        ];
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
                    'additionalBegin' => 1,
                    'additionalEnd' => 1
                ],
                'expected' => [
                    'resultsCount' => 40,
                    'resultsNull' => 4,
                    'totalPrice' => 4400,
                    'okResult' => 6,
                    'errorResult' => 34

                ],
                'mockResults' => $mockResults2

            ]
        ];
    }

    private function resultGenerate(int $amount, ?string $status): array
    {
        $result = [];
        for ($i = 1; $i <= $amount; $i++ ) {
            if (null === $status) {
                $result[] = null;
            } else {
                $result[] = (new Result())->setStatus($status);
            }

        }

        return $result;
    }



}