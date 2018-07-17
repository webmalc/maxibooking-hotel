<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxStay;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxStayTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoMaxStay(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStay = new MaxStay();
        $maxStay->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxStayViolation(SearchQuery $searchQuery, array $restriction):void
    {
        $maxStay = new MaxStay();
        $restriction[1]['maxStay'] = 1;
        $this->expectExceptionMessage('Room maxStay at '. Helper::convertMongoDateToDate($restriction[1]['date'])->format('d-m-Y'));
        $maxStay->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMaxStayNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $maxStay = new MaxStay();
        $restriction[1]['maxStay'] = 5;
        $maxStay->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}