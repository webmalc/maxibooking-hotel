<?php


namespace MBH\Bundle\SearchBundle\Lib;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;

class HotelContentHolder
{
    /** @var Tariff[] */
    private $tariffs;

    /** @var RoomType[] */
    private $roomTypes;

    /**
     * TariffHolder constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     */
    public function __construct(TariffRepository $tariffRepository, RoomTypeRepository $roomTypeRepository)
    {
        $this->tariffs = $tariffRepository->findAll();
        $this->roomTypes = $roomTypeRepository->findAll();
    }

    /**
     * @param string $tariffId
     * @return Tariff|null
     */
    public function getFetchedTariff(string $tariffId): ?Tariff
    {
        foreach ($this->tariffs as $tariff) {
            if ($tariffId === $tariff->getId()) {
                return $tariff;
            }
        }

        return null;
    }

    public function getFetchedRoomType(string $roomTypeId): ?RoomType
    {
        foreach ($this->roomTypes as $roomType) {
            if ($roomTypeId === $roomType->getId()) {
                return $roomType;
            }
        }

        return null;
    }


}