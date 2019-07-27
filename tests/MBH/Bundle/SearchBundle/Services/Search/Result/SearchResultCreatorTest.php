<?php

namespace Tests\MBH\Bundle\SearchBundle\Services\Search\Result;

use DateTime;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SearchResultCreatorTest extends SearchWebTestCase
{

    public function testCreateResult()
    {
        $creator = $this->getContainer()->get('mbh_search.search_result_creator');
        $begin = new DateTime('midnight');
        $end = new DateTime('midnight + 1 day');
        $searchQuery = new SearchQuery();
        $searchQuery
            ->setBegin($begin)
            ->setEnd($end)
            ->setTariffId('fakeTariffId')
            ->setRoomTypeId('fakeRoomTypeId')
            ->setAdults(1)
            ->setChildren(2)
            ->setChildrenAges([10, 12])
        ;

        $prices = [
            '1_2' => [
                'someprice'
            ]
        ];

        $actual = $creator->createResult($searchQuery, $prices, 3);
        $this->assertSame('fakeTariffId', $actual->getTariff());
        $this->assertSame('fakeRoomTypeId', $actual->getRoomType());
        $this->assertEquals($begin, $actual->getBegin());
        $this->assertEquals($end, $actual->getEnd());

    }
}
