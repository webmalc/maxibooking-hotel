<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ClientConfigRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;

class RoomTypeFetcher
{
    /** @var bool */
    private $isUseCategory = false;

    /** @var RoomTypeRepository */
    private $roomTypeRepository;

    /**
     * RoomTypeFetcher constructor.
     * @param DocumentManager $documentManager
     * @param RoomTypeRepository $roomTypeRepository
     * @param ClientConfigRepository $configRepository
     */
    public function __construct(
        DocumentManager $documentManager,
        RoomTypeRepository $roomTypeRepository,
        ClientConfigRepository $configRepository
    ) {
        $this->isUseCategory = $configRepository->fetchConfig()->getUseRoomTypeCategory();
        $this->roomTypeRepository = $roomTypeRepository;
    }

    public function fetch(array $ids = [], array $hotelIds = []): array
    {
        if ($this->isUseCategory) {
            $roomTypeIds = $this->roomTypeRepository->fetchRawWithCategory($ids, $hotelIds);
        } else {
            $roomTypeIds = $this->roomTypeRepository->fetchRaw($ids, $hotelIds);
        }

        return $roomTypeIds;
    }


}