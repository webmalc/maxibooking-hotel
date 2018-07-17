<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxBeforeArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxBeforeArrivalTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testNoCloseOnMaxBeforeArrivalCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $maxBeforeArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testOnMaxBeforeArrivalTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $restriction[0]['maxBeforeArrival'] = 4;
        $this->expectExceptionMessage('Room maxBeforeArrival at ' . Helper::convertMongoDateToDate($restriction[0]['date'])->format('d-m-Y'));
        $maxBeforeArrival->check($searchQuery, $restriction);

    }

    /** @dataProvider dataProvider
     * @param SearchQuery $searchQuery
     * @param array $restriction
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\RestrictionsCheckerException
     */
    public function testOnMaxBeforeArrivalNotTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $restriction[0]['maxBeforeArrival'] = 6;
        $maxBeforeArrival->check($searchQuery, $restriction);
        $this->assertTrue(true);
    }

    public function dataProvider(): iterable
    {
        $daysBeforeArrival = 6;
        $now = new \DateTime('midnight');
        $begin = (clone $now)->modify("+{$daysBeforeArrival} days");
        $end = (clone $begin)->modify('+2 days');
        $searchQuery =
            (new SearchQuery())
                ->setBegin(clone $begin)
                ->setEnd(clone $end)
                ->setAdults(2)
                ->setChildren(1)
                ->setChildrenAges([2]);

        $restrictions = [];

        foreach (new \DatePeriod(
                     clone $begin,
                     \DateInterval::createFromDateString('1 day'),
                     (clone $end)->modify('+ 1 day')
                 ) as $day) {
            /** @var \DateTime $dat */
            $restrictions[] = [
                'date' => new \MongoDate(strtotime($day->format('Y-m-d'))),
            ];

        }

        yield [$searchQuery, $restrictions];
    }


}