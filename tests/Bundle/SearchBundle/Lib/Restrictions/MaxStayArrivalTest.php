<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxStayArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxStayArrivalTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider */
    public function testNoMaxStayArrival(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStayArrival = new MaxStayArrival();
        $this->assertNull($maxStayArrival->check($searchQuery, $restriction));
    }

    /** @dataProvider dataProvider */
    public function testMaxstayViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxStayArrival = new MaxStayArrival();
        $restriction[0]['maxStayArrival'] = 1;
        $this->expectExceptionMessage('Room maxStayArrival at '. $restriction[0]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $maxStayArrival->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider */
    public function testMaxstayNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxStayArrival = new MaxStayArrival();
        $restriction[1]['maxStayArrival'] = 1;
        $this->assertNull($maxStayArrival->check($searchQuery, $restriction));
    }
}