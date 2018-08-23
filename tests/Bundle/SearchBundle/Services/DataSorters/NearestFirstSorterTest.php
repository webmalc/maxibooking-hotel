<?php


namespace Tests\Bundle\SearchBundle\Services\DataSorters;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\SearchBundle\Services\DateSorters\NearestFirstSorter;

class NearestFirstSorterTest extends WebTestCase
{
    /** @dataProvider dataProvider */
    public function testSort(\DateTime $begin, \DateTime $end, $dates)
    {
        $sorter = new NearestFirstSorter();
        $actual = $sorter->sort($begin, $end, $dates);
        $actualFirst = reset($actual);
        $this->assertEquals($begin->getTimestamp(), $actualFirst['begin']->getTimeStamp());
        $this->assertEquals($end->getTimestamp(), $actualFirst['end']->getTimeStamp());
    }

    public function dataProvider()
    {
        $begin = new \DateTime('10-01-2018 midnight');
        $end = new \DateTime('15-01-2018 midnight');
        $additionalBegin = 1;
        $additionalEnd = 1;
        $dates = [];
        foreach (new \DatePeriod((clone $begin)->modify("- ${additionalBegin} days"), \DateInterval::createFromDateString('1 day'), (clone $begin)->modify("+ ${additionalBegin} days + 1 day")) as $beginDay) {
            foreach (new \DatePeriod((clone $end)->modify("- ${additionalEnd} days"), \DateInterval::createFromDateString('1 day'), (clone $end)->modify("+ ${additionalEnd} days + 1 day")) as $endDay) {
                if($beginDay < $endDay)
                $dates[$beginDay->format('d-m-Y') . '_' . $endDay->format('d-m-Y')] = [
                    'begin' => $beginDay,
                    'end' => $endDay
                ];
            }
        }

        return [
            [
                'begin' => $begin,
                'end' => $end,
                'dates' => $dates
            ]
        ];
    }
}