<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\ClosedOnDeparture;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedOnDepartureTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoCloseOnDepartureCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $closedOnDeparture->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnDepartureMiddleCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[1]['closedOnDeparture'] = true;
        $closedOnDeparture->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnDepartureBeginCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[0]['closedOnDeparture'] = true;
        $closedOnDeparture->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnDepartureEndCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[2]['closedOnDeparture'] = true;
        $end = $searchQuery->getEnd()->format('d-m-Y');
        $this->expectExceptionMessage('Room closedOnDeparture at '.$end);
        $closedOnDeparture->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseOnDepartureEndCheckNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $closedOnDeparture = new ClosedOnDeparture();
        $restriction[2]['closedOnDeparture'] = false;
        $closedOnDeparture->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}