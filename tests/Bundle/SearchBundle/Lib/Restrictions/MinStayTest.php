<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;



use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MinStay;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinStayTest extends RestrictionWebTestCase
{
    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoMinStay(SearchQuery $searchQuery, array $restriction): void
    {
        $maxSta = new MinStay();
        $maxSta->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinStayViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $minStay = new MinStay();
        $restriction[1]['minStay'] = 5;
        $this->expectExceptionMessage('Room minStay at '. Helper::convertMongoDateToDate($restriction[1]['date'])->format('d-m-Y'));
        $minStay->check($searchQuery, $restriction);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testMinStayNoViolation(SearchQuery $searchQuery, array $restriction): void
    {
        $minStay = new MinStay();
        $restriction[1]['minStay'] = 2;
        $minStay->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }
}
