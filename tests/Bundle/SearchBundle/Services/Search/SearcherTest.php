<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchResult;

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
            ->setAdditionalBegin(7)
        ;

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


}