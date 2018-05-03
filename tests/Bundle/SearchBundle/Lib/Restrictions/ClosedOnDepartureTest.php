<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\ClosedOnDeparture;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnDepartureTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider */
    public function testNoCloseOnDepartureCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $this->assertNull($closedOnDeparture->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnDepartureMiddleCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[1]['closedOnDeparture'] = true;
        $this->assertNull($closedOnDeparture->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnDepartureBeginCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[0]['closedOnDeparture'] = true;
        $this->assertNull($closedOnDeparture->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseOnDepartureEndCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[2]['closedOnDeparture'] = true;
        $this->expectExceptionMessage('Room closedOnDeparture at 03-06-2018');
        $this->assertNull($closedOnDeparture->check($searchQuery, $restriction));

    }
}