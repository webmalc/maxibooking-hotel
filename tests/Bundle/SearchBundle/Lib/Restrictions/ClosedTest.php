<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\Closed;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class ClosedTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider */
    public function testNoCloseCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closed = new Closed();
        $this->assertNull($closed->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testCloseCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $closed = new Closed();
        $restriction[2]['closed'] = true;
        $this->expectExceptionMessage('Room closed in 03-06-2018');
        $closed->check($searchQuery, $restriction);

    }

}