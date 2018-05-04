<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxGuest;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxGuestTest extends RestrictionWebTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testNoMaxGuest(SearchQuery $searchQuery, array $restriction)
    {
        $maxGuest = new MaxGuest();
        $this->assertNull($maxGuest->check($searchQuery, $restriction));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMaxGuestViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxGuest = new MaxGuest();
        $restriction[1]['maxGuest'] = 2;
        $this->expectExceptionMessage('Room maxGuest at '. $restriction[1]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $maxGuest->check($searchQuery, $restriction);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMaxGuestNoViolation(SearchQuery $searchQuery, array $restriction)
    {
        $maxGuest = new MaxGuest();
        $restriction[1]['maxGuest'] = 4;
        $this->assertNull($maxGuest->check($searchQuery, $restriction));
    }
}