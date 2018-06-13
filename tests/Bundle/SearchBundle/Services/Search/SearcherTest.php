<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearcherTest extends SearchWebTestCase
{
    /** @dataProvider dataProvider */
    public function testSearch($data)
    {
        $searchQueries = $this->createSearchQueries($data);

        $searcher = $this->getContainer()->get('mbh_search.searcher');

        foreach ($searchQueries as $searchQuery) {
            try {
                $actual[] = $searcher->search($searchQuery);
            } catch (SearchException $e) {
                $errors['searchError'][] = $e->getMessage();
            }
        }

        $this->assertTrue(true);
    }

    public function dataProvider(): iterable
    {
        yield [
            [
                'beginOffset' => 1,
                'endOffset' => 8,
                'tariffFullTitle' => '',
                'roomTypeFullTitle' => '',
                'hotelFullTitle' => 'Отель Волга',
                'adults' => 1,
                'children' => 1,
                'childrenAges' => [5],
            ]
        ];
    }




}