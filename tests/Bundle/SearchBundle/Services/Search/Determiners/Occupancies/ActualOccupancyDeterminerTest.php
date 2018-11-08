<?php


namespace Tests\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\CommonOccupancyDeterminer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminer as ActualDeterminer;

class ActualOccupancyDeterminerTest extends WebTestCase
{
    public function testDetermine(): void
    {
        $dataFetcher = $this->createMock(SharedDataFetcher::class);
        $dataFetcher->expects($this->once())->method('getFetchedTariff')->willReturn(new Tariff());
        $dataFetcher->expects($this->once())->method('getFetchedRoomType')->willReturn(new RoomType());

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch');

        $determiner = $this->createMock(CommonOccupancyDeterminer::class);
        $determiner->expects($this->once())->method('determine');

        $service = new ActualDeterminer($dispatcher, $determiner, $dataFetcher);

        $searchQuery = new SearchQuery();
        $searchQuery
            ->setTariffId('fakeTariffId')
            ->setRoomTypeId('fakeRoomTypeId')
            ->setAdults(1)
            ->setChildren(1)
            ->setChildrenAges([1]);

        $service->determine($searchQuery, 'fakeEvent');

    }
}