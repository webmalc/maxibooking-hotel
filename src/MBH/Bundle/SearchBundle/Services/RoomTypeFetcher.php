<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;

class RoomTypeFetcher
{
    /** @var bool */
    private $isUseCategory = false;

    /** @var RoomTypeRepository */
    private $roomTypeRepository;

    /**
     * RoomTypeFetcher constructor.
     * @param bool $isUseCategory
     * @param DocumentManager $documentManager
     * @param RoomTypeRepository $roomTypeRepository
     */
    public function __construct(
        DocumentManager $documentManager,
        RoomTypeRepository $roomTypeRepository
    ) {
        $this->isUseCategory = $documentManager
            ->getRepository(ClientConfig::class)
            ->fetchConfig()->getUseRoomTypeCategory();
        $this->roomTypeRepository = $roomTypeRepository;
    }

    public function fetch(array $ids = [], array $hotelIds = []): array
    {
        if ($this->isUseCategory) {
            /** TODO: Не факт что это сработает возможно придется забирать со стороны категорий */
            $roomTypeIds = $this->roomTypeRepository->fetchRawWithCategory($ids, $hotelIds);
        } else {
            $roomTypeIds = $this->roomTypeRepository->fetchRaw($ids, $hotelIds);
        }

        return $roomTypeIds;
    }


}