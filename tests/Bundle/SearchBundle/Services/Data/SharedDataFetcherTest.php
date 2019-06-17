<?php


namespace Tests\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use Tests\Bundle\SearchBundle\SearchWebTestCase;

class SharedDataFetcherTest extends SearchWebTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetFetchedTariff($data): void
    {
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy(['fullTitle' => $data['hotelName']]);
        $allTariffs = $hotel->getTariffs()->toArray();
        /** @var Tariff $tariff */
        $tariff = $this->getDocumentFromArrayByFullTitle($allTariffs, $data['tariffName']);
        $service = $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $actual = $service->getFetchedTariff($tariff->getId());
        $this->assertEquals($data['tariffName'], $actual->getFullTitle());
    }

    /** @dataProvider dataProvider */
    public function testGetFetchedRoomType($data): void
    {
        $hotel = $this->dm->getRepository(Hotel::class)->findOneBy(['fullTitle' => $data['hotelName']]);
        $allRoomTypes = $hotel->getRoomTypes()->toArray();
        $roomType = $this->getDocumentFromArrayByFullTitle($allRoomTypes, $data['roomTypeName']);
        $service = $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $actual = $service->getFetchedRoomType($roomType->getId());
        $this->assertEquals($data['roomTypeName'], $actual->getFullTitle());
    }

    public function testGetFetchedTariffFail(): void
    {
        $service = $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $this->expectException(SharedFetcherException::class);
        $service->getFetchedTariff('No valid Id');
    }

    public function testGetFetchedRoomTypeFail(): void
    {
        $service = $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $this->expectException(SharedFetcherException::class);
        $service->getFetchedRoomType('No valid Id');
    }

    public function testFetcherIsActuallyShared(): void
    {
        $tariffRepositoryMock = $this->createMock(TariffRepository::class);
        $tariffRepositoryMock->method('getClassName')->willReturn('Tariff');
        $tariffRepositoryMock->expects($this->once())->method('findAll')->willReturn([new Tariff()]);
        $this->getContainer()->set('mbh_search.tariff_repository', $tariffRepositoryMock);

        $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $fetcher = $this->getContainer()->get('mbh_search.shared_data_fetcher');
        $this->expectException(SharedFetcherException::class);
        $fetcher->getFetchedTariff('fakeId');

    }

    public function dataProvider()
    {
        yield [
            [
                'hotelName' => 'Отель Волга',
                'tariffName' => 'Основной тариф',
                'roomTypeName' => 'Люкс'
            ]
        ];
    }


}