<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use Doctrine\Common\Persistence\ObjectRepository;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;

/**
 * Class SharedDataFetcher
 * @package MBH\Bundle\SearchBundle\Services\Data
 */
class SharedDataFetcher implements SharedDataFetcherInterface
{
    /** @var array */
    private $data;
    /**
     * @var TariffRepository
     */
    private $tariffRepository;
    /**
     * @var RoomTypeRepository
     */
    private $roomTypeRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    /**
     * SharedDataFetcher constructor.
     * @param TariffRepository $tariffRepository
     * @param RoomTypeRepository $roomTypeRepository
     * @param RoomRepository $roomRepository
     */
    public function __construct(
        TariffRepository $tariffRepository,
        RoomTypeRepository $roomTypeRepository,
        RoomRepository $roomRepository
    ) {

        $this->tariffRepository = $tariffRepository;
        $this->roomTypeRepository = $roomTypeRepository;
        $this->roomRepository = $roomRepository;
    }

    /**
     * @param string $tariffId
     * @return Tariff
     * @throws SharedFetcherException
     */
    public function getFetchedTariff(string $tariffId): Tariff
    {
        return $this->getObject($this->tariffRepository, $tariffId);
    }

    /**
     * @param string $roomTypeId
     * @return RoomType
     * @throws SharedFetcherException
     */
    public function getFetchedRoomType(string $roomTypeId): RoomType
    {
        return $this->getObject($this->roomTypeRepository, $roomTypeId);
    }

    /**
     * @param string $roomId
     * @return string
     * @throws SharedFetcherException
     */
    public function getRoomTypeIdOfRoomId(string $roomId): string
    {
        $room =  $this->getObject($this->roomRepository, $roomId);

        return $room->getRoomType()->getId();
    }

    /**
     * @param ObjectRepository $repository
     * @param string $objectId
     * @return RoomType|Tariff|Room
     * @throws SharedFetcherException
     */
    private function getObject(ObjectRepository $repository, string $objectId)
    {
        $objectName = $repository->getClassName();
        if (!isset($this->data[$objectName])) {
            $objects = $repository->findAll();
            $this->fillDataHolder($objectName, $objects);
        }
        $object = $this->data[$objectName][$objectId] ?? null;
        if ($object === null) {
            throw new SharedFetcherException('There is no %s in %s!', $objectName, __CLASS__);
        }

        return $object;
    }

    /**
     * @param string $varName
     * @param Base[] $objects
     */
    private function fillDataHolder(string $varName, array $objects): void
    {
        foreach ($objects as $object) {
            $this->data[$varName][$object->getId()] = $object;
        }
    }

    //** TODO: Реализовать методы для замены dataHolder который использует  только генератор searchQuery */
}