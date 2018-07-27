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

    public function __construct(TariffRepository $tariffRepository, RoomTypeRepository $roomTypeRepository, RoomRepository $roomRepository)
    {
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAllWithHotels();
        $this->rooms = $roomRepository->findAll();
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

    /**
     * @param string $roomId
     * @return string
     * @throws SharedFetcherException
     */
    public function getRoomTypeIdOfRoomId(string $roomId): string
    {
        foreach ($this->rooms as $room) {
            if ($roomId === $room->getId()) {
                return $room->getRoomType()->getId();
            }
        }

        throw new SharedFetcherException('Can not determine RoomTypeId by RoomId');
    }


}