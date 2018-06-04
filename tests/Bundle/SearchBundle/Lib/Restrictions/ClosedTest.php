<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\Closed;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoCloseCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closed = new Closed();
        $closed->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testCloseCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closed = new Closed();
        $restriction[2]['closed'] = true;
        $end = $searchQuery->getEnd()->format('d-m-Y');
        $this->expectExceptionMessage('Room closed in '.$end);
        $closed->check($searchQuery, $restriction);
    }

}