<?php


namespace Tests\Bundle\SearchBundle\Lib\Restrictions;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\CommonOccupancyDeterminer;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\Occupancy;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer;

abstract class RestrictionWebTestCase extends WebTestCase
{
    protected $determiner;

    protected $occupancy;

    public function setUp()
    {
        $this->determiner = $this->createMock(OccupancyDeterminer::class);
        $this->determiner->method('determine')->willReturnCallback(function() {
            return $this->occupancy;
        });

    }

    public function dataProvider(): iterable
    {
        $now = new \DateTime('midnight');
        $begin = clone $now;
        $end = (clone $begin)->modify('+2 days');
        $searchQuery =
            (new SearchQuery())
                ->setBegin(clone $begin)
                ->setEnd(clone $end)
                ->setAdults(2)
                ->setChildren(2)
                ->setChildrenAges([1,3])
        ;

        $restrictions = [];

        foreach (new \DatePeriod(
                     clone $begin,
                     \DateInterval::createFromDateString('1 day'),
                     (clone $end)->modify('+ 1 day')
                 ) as $day) {

            $restrictions[] = [
                'date' => new \MongoDate(strtotime($day->format('Y-m-d'))),
            ];

        }

        yield [$searchQuery, $restrictions];

    }

    protected function createOccupancy(SearchQuery $searchQuery): void
    {
        $determiner = new CommonOccupancyDeterminer();
        $tariff = new Tariff();
        $tariff->setInfantAge(2);
        $tariff->setChildAge(14);
        $roomType = new RoomType();
        $roomType->setMaxInfants(2);

        $this->occupancy = $determiner->determine(Occupancy::createInstanceBySearchQuery($searchQuery), $tariff, $roomType);
    }
}