<?php


namespace Tests\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\SearchResultComposer;

class SearchResultComposerTest extends WebTestCase
{
    /** @var SearchResultComposer */
    private $searchComposer;

    public function setUp()
    {
        $this->searchComposer = $this->getContainer()->get('mbh_search.result_composer');
    }

    public function testComposeResult(): void
    {
        $conditions = new SearchConditions();
        $begin = new \DateTime("midnight + 1 day");
        $end = new \DateTime("midnight + 8 day");

        $conditions
            ->setBegin($begin)
            ->setEnd($end)
            ->setAdults(1)
            ->setChildren(3)
            ->setChildrenAges([1,5,8])

        ;

        $searchQueries = $this
            ->getContainer()
            ->get('mbh_search.search_query_generator')
            ->generateSearchQueries($conditions)
        ;
        /** @var SearchQuery $searchQuery */
        $searchQuery = reset($searchQueries);
        $searchResult = new SearchResult();
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $roomType = $dm->find(RoomType::class, $searchQuery->getRoomTypeId());
        $tariff = $dm->find(Tariff::class, $searchQuery->getTariffId());
        $roomCaches = $this->getContainer()->get('mbh_search.room_cache_search_provider')->fetchAndCheck($searchQuery->getBegin(), $searchQuery->getEnd(), $roomType, $tariff);

        $this->searchComposer->composeResult($searchResult, $searchQuery, $roomType, $tariff, $roomCaches);
//        $actual = $this->searchComposer->composeResult($searchResult, $roomCaches, $searchQuery, $roomType, $tariff);
    }

    public function dataProvider(): array
    {

    }
}