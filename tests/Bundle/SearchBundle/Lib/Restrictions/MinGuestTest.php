<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;;


use MBH\Bundle\SearchBundle\Lib\Restrictions\MinGuest;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinGuestTest extends RestrictionWebTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testNoMinGuest(SearchQuery $searchQuery, array $restriction)
    {
        $minGuest = new MinGuest();
        $this->assertNull($minGuest->check($searchQuery, $restriction));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMinGuestViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minGuest = new MinGuest();
        $restriction[1]['minGuest'] = 4;
        $this->expectExceptionMessage('Room minGuest at '. $restriction[1]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $minGuest->check($searchQuery, $restriction);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMinGuestNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $minGuest = new MinGuest();
        $restriction[1]['minGuest'] = 2;
        $this->assertNull($minGuest->check($searchQuery, $restriction));
    }
}