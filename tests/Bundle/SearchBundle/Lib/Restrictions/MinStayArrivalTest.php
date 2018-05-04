<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Restrictions\MinStayArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinStayArrivalTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider */
    public function testNoMinStayArrival(SearchQuery $searchQuery, array $restriction): void
    {
        $minStayArrival = new MinStayArrival();
        $this->assertNull($minStayArrival->check($searchQuery, $restriction));
    }

    /** @dataProvider dataProvider */
    public function testMinstayViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minStayArrival = new MinStayArrival();
        $restriction[0]['minStayArrival'] = 4;
        $this->expectExceptionMessage('Room minStayArrival at '. $restriction[0]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $minStayArrival->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider */
    public function testMinstayNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minStayArrival = new MinStayArrival();
        $restriction[1]['minStayArrival'] = 4;
        $this->assertNull($minStayArrival->check($searchQuery, $restriction));
    }
}
