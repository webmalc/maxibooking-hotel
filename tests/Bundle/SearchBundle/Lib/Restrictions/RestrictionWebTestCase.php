<?php


namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

abstract class RestrictionWebTestCase extends WebTestCase
{

    public function dataProvider(): iterable
    {
        $begin = new \DateTime('01-06-2018 midnight');
        $end = new \DateTime('03-06-2018 midnight');
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