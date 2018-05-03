<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\ClosedOnArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnArrivalTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider */
    public function testNoCloseOnArrivalCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $this->assertNull($closedOnArrival->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnArrivalMiddleCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[1]['closedOnArrival'] = true;
        $this->assertNull($closedOnArrival->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnArrivalBeginCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[0]['closedOnArrival'] = true;
        $this->expectExceptionMessage('Room closedOnArrival at 01-06-2018');
        $this->assertNull($closedOnArrival->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnArrivalEndCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[2]['closedOnArrival'] = true;
        $this->assertNull($closedOnArrival->check($searchQuery, $restriction));

    }



}