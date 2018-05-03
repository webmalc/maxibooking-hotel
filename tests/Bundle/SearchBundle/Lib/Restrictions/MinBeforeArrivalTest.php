<?php

namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\SearchBundle\Lib\Restrictions\MinBeforeArrival;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

class MinBeforeArrivalTest extends RestrictionWebTestCase
{

    /** @dataProvider dataProvider */
    public function testNoCloseOnMinBeforeArrivalCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $minBeforeArrival = new MinBeforeArrival();
        $this->assertNull($minBeforeArrival->check($searchQuery, $restriction));

    }

    /** @dataProvider dataProvider */
    public function testOnMinBeforeArrivalTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $minBeforeArrival = new MinBeforeArrival();
        $restriction[0]['minBeforeArrival'] = 7;
        $this->expectExceptionMessage('Room minBeforeArrival at '. $restriction[0]['date']->toDateTime()->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('d-m-Y'));
        $minBeforeArrival->check($searchQuery, $restriction);

    }

    /** @dataProvider dataProvider */
    public function testOnMinBeforeArrivalNotTriggeredCheck(SearchQuery $searchQuery, array $restriction): void
    {
        $maxBeforeArrival = new MinBeforeArrival();
        $restriction[0]['minBeforeArrival'] = 5;
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