<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\ClosedOnArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnArrivalTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoCloseOnArrivalCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $closedOnArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);

    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnArrivalMiddleCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[1]['closedOnArrival'] = true;
        $closedOnArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);

    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnArrivalBeginCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[0]['closedOnArrival'] = true;
        $begin = $searchQuery->getBegin()->format('d-m-Y');
        $this->expectExceptionMessage('Room closedOnArrival at '.$begin);
        $closedOnArrival->check($searchQuery, $restriction);

    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnArrivalEndCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[2]['closedOnArrival'] = true;
        $closedOnArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnArrivalBeginCheckNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnArrival = new ClosedOnArrival();
        $restriction[0]['closedOnArrival'] = false;
        $closedOnArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }



}