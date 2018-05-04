<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxStay;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxStayTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider */
    public function testNoMinstay(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStay = new MaxStay();
        $this->assertNull($maxStay->check($searchQuery, $restriction));
    }

    /** @dataProvider dataProvider */
    public function testMinstayViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxStay = new MaxStay();
        $restriction[1]['maxStay'] = 1;
        $this->expectExceptionMessage('Room maxStay at '. $restriction[1]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $maxStay->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider */
    public function testMinstayNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxStay = new MaxStay();
        $restriction[1]['maxStay'] = 5;
        $this->assertNull($maxStay->check($searchQuery, $restriction));
    }
}