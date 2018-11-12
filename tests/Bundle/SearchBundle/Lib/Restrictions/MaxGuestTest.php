<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxGuest;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxGuestTest extends RestrictionWebTestCase
{

    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoMaxGuest(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $maxGuest = new MaxGuest($this->determiner);
        $maxGuest->check($searchQuery, $restriction);
        $this->assertTrue(true);

    }

    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxGuestViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $maxGuest = new MaxGuest($this->determiner);
        $restriction[1]['maxGuest'] = 2;
        $this->expectExceptionMessage('Room maxGuest at '. $restriction[1]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $maxGuest->check($searchQuery, $restriction);
    }

    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxGuestNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $maxGuest = new MaxGuest($this->determiner);
        $restriction[1]['maxGuest'] = 3;
        $maxGuest->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}