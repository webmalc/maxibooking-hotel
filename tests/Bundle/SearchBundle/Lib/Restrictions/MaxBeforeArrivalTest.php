<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\MaxBeforeArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MaxBeforeArrivalTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider */
    public function testNoCloseOnMaxBeforeArrivalCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $this->assertNull($maxBeforeArrival->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testOnMaxBeforeArrivalTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $restriction[0]['maxBeforeArrival'] = 4;
        $this->expectExceptionMessage('Room maxBeforeArrival at '. $restriction[0]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $maxBeforeArrival->check($searchQuery, $restriction);

    }

    /** @dataProvider dataProvider */
    public function testOnMaxBeforeArrivalNotTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MaxBeforeArrival();
        $restriction[0]['maxBeforeArrival'] = 6;
        $this->assertNull($maxBeforeArrival->check($searchQuery, $restriction));

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