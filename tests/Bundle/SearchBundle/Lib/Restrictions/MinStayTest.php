<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;



use MBH\Bundle\SearchBundle\Lib\Restrictions\MinStay;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinStayTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider */
    public function testNoMinstay(SearchQuery $searchQuery, array $restriction): void
    {
        $maxSta = new MinStay();
        $this->assertNull($maxSta->check($searchQuery, $restriction));
    }

    /** @dataProvider dataProvider */
    public function testMinstayViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minStay = new MinStay();
        $restriction[1]['minStay'] = 5;
        $this->expectExceptionMessage('Room minStay at '. $restriction[1]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $minStay->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider */
    public function testMinstayNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minStay = new MinStay();
        $restriction[1]['minStay'] = 2;
        $this->assertNull($minStay->check($searchQuery, $restriction));
    }
}
