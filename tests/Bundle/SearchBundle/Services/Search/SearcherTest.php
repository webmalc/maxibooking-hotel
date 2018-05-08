<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchResult;
use MBH\Bundle\SearchBundle\Services\Search\Searcher;

class SearcherTest extends WebTestCase
{
    public function testSearch()
    {
        $conditions = new SearchConditions();
        $begin = new \DateTime("midnight + 1 day");
        $end = new \DateTime("midnight + 4 day");

        $conditions
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdults(1)
            ->setChildren(1)
            ->setChildrenAges([5])
            ->setAdditionalBegin(7);

        $searchQueries = $this
            ->getContainer()
            ->get('mbh_search.search_query_generator')
            ->generateSearchQueries($conditions);

        $searcher = $this->getContainer()->get('mbh_search.searcher');

        foreach ($searchQueries as $searchQuery) {
            $actual = $searcher->search($searchQuery);
            $this->assertInstanceOf(SearchResult::class, $actual);
        }

    }

    /**
     * @dataProvider populationDataProvider
     */
    public function testCheckRoomTypePopulationLimit(int $places, int $additionalPlaces, int $maxInfants, bool $expectedSuccess)
    {
        $roomType = new RoomType();
        $roomType->setPlaces($places)->setAdditionalPlaces($additionalPlaces)->setMaxInfants($maxInfants);
        $searchQuery = new SearchQuery();
        $searchQuery
            ->setChildAge(7)
            ->setInfantAge(2)
            ->setAdults(2)
            ->setChildren(4)
            ->setChildrenAges([1, 1, 6, 6]);
        $reflector = new \ReflectionClass(Searcher::class);
        $method = $reflector->getMethod('checkRoomTypePopulationLimit');
        $method->setAccessible(true);
        $searcher = $this->getContainer()->get('mbh_search.searcher');

        if (!$expectedSuccess) {
            $this->expectException(SearchException::class);
        }

        $this->assertNull($method->invoke($searcher, $roomType, $searchQuery));

    }

    public function populationDataProvider()
    {
        return [
            [3, 1, 2, true],
            [3, 0, 2, false ],
            [6, 0, 1, false ]
        ];
    }


}