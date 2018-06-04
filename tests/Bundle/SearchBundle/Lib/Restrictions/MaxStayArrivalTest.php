<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxStayArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxStayArrivalTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoMaxStayArrival(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStayArrival = new MaxStayArrival();
        $maxStayArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxStayViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStayArrival = new MaxStayArrival();
        $restriction[0]['maxStayArrival'] = 1;
        $this->expectExceptionMessage('Room maxStayArrival at ' . Helper::convertMongoDateToDate($restriction[0]['date'])->format('d-m-Y'));
        $maxStayArrival->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxStayNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStayArrival = new MaxStayArrival();
        $restriction[1]['maxStayArrival'] = 1;
        $maxStayArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);

    }
}