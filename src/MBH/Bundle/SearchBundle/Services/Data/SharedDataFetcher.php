<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;

class SharedDataFetcher implements SharedDataFetcherInterface
{
    /** @var Tariff[] */
    private $tariffs;

    /** @var RoomType[] */
    private $roomTypes;

    /** @var Room[] */
    private $rooms;

    /** @var array */
    private $hotelGrouped;

    public function __construct(TariffRepository $tariffRepository, RoomTypeRepository $roomTypeRepository, RoomRepository $roomRepository)
    {
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAll();
        $this->rooms = $roomRepository->findAll();
//        $this->groupDataByHotelId();

    }

    private function groupDataByHotelId(): void

    {
        foreach ($this->tariffs as $tariff) {
            $hotelId = $tariff->getHotel()->getId();
            $this->hotelGrouped[$hotelId]['tariffs'][] = $tariff;
        }

        foreach ($this->roomTypes as $roomType) {
            $hotelId = $roomType->getHotel()->getId();
            $this->hotelGrouped[$hotelId]['roomTypes'][] = $roomType;
        }
    }

    /**
     * @param string $tariffId
     * @return Tariff
     * @throws SharedFetcherException
     */
    public function getFetchedTariff(string $tariffId): Tariff
    {
        foreach ($this->tariffs as $tariff) {
            if ($tariffId === $tariff->getId()) {
                return $tariff;
            }
        }

        throw new SharedFetcherException('There is no Tariff in tariff holder!');

    }

    /**
     * @param string $roomTypeId
     * @return RoomType
     * @throws SharedFetcherException
     */
    public function getFetchedRoomType(string $roomTypeId): RoomType
    {
        foreach ($this->roomTypes as $roomType) {
            if ($roomTypeId === $roomType->getId()) {
                return $roomType;
            }
        }

        throw new SharedFetcherException('There is no RoomType in RoomTypeHolder!');
    }

    public function getRoomTypeIdOfRoomId(string $roomId): string
    {
        foreach ($this->rooms as $room) {
            if ($roomId === $room->getId()) {
                return $room->getRoomType()->getId();
            }
        }
        throw new SharedFetcherException('Can not determine RoomTypeId by RoomId');
    }

    //** TODO: Реализовать методы для замены dataHolder который использует  только генератор searchQuery */
}