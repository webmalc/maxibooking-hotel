<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MinGuest;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinGuestTest extends RestrictionWebTestCase
{
    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoMinGuest(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $minGuest = new MinGuest($this->determiner);
        $minGuest->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinGuestViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $minGuest = new MinGuest($this->determiner);
        $restriction[1]['minGuest'] = 5;
        $this->expectExceptionMessage('Room minGuest at '. Helper::convertMongoDateToDate($restriction[1]['date'])->format('d-m-Y'));
        $minGuest->check($searchQuery, $restriction);
    }

    /**
     * @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinGuestNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $this->createOccupancy($searchQuery);
        $minGuest = new MinGuest($this->determiner);
        $restriction[1]['minGuest'] = 2;
        $minGuest->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}