<?php


namespace MBH\Bundle\SearchBundle\Lib;


use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\HotelBundle\Document\HotelRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;

/**
 * Class DataHolder
 * @deprecated 
 * @package MBH\Bundle\SearchBundle\Lib
 */
class DataHolder
{
    /** @var TariffRepository */
    private $tariffRepository;

    /** @var RoomTypeRepository */
    private $roomTypeRepository;

    /** @var array */
    private $hotelIdsInSearch;

    /**
     * TariffHolder constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     * @param HotelRepository $hotelRepository
     * @throws MongoDBException
     */
    public function __construct(
        TariffRepository $tariffRepository,
        RoomTypeRepository $roomTypeRepository,
        HotelRepository $hotelRepository
    ) {
        $this->tariffRepository = $tariffRepository;
        $this->roomTypeRepository = $roomTypeRepository;
        $this->hotelIdsInSearch = $hotelRepository->getSearchActiveIds();
    }



    /**
     * @param array $hotelIds
     * @param array $tariffIds
     * @param bool $isEnabled
     * @param bool $isOnline
     * @return array
     * @throws MongoDBException
     */
    public function getTariffsRaw(array $hotelIds, array $tariffIds, bool $isEnabled, bool $isOnline): array
    {
        return $this->tariffRepository->fetchRaw(
            $hotelIds,
            $tariffIds,
            $isEnabled,
            $isOnline
        );
    }

    /**
     * @param iterable $rawRoomTypeIds
     * @param array $hotelIds
     * @return array
     */
    public function getRoomTypesRaw(iterable $rawRoomTypeIds, array $hotelIds): array
    {
        return $this->roomTypeRepository->fetchRaw($rawRoomTypeIds, $hotelIds);
    }


    public function getHotelIdsInSearch(): array
    {
        return $this->hotelIdsInSearch;
    }


}