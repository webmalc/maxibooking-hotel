<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MinStayArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinStayArrivalTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider */
    public function testNoMinStayArrival(SearchQuery $searchQuery, array $restriction): void
    {
        $minStayArrival = new MinStayArrival();
        $minStayArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);

    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinstayViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $minStayArrival = new MinStayArrival();
        $restriction[0]['minStayArrival'] = 4;
        $this->expectExceptionMessage('Room minStayArrival at '. Helper::convertMongoDateToDate($restriction[0]['date'])->format('d-m-Y'));
        $minStayArrival->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinstayNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $minStayArrival = new MinStayArrival();
        $restriction[1]['minStayArrival'] = 4;
        $minStayArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}
