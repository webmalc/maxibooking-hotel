<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;

class RoomRawFetcher implements DataRawFetcherInterface
{

    /** @var string  */
    public const  NAME = 'roomFetcher';

    /** @var RoomRepository */
    private $roomRepository;
    /**
     * @var SharedDataFetcher
     */
    private $sharedDataFetcher;

    /**
     * RoomRawFetcher constructor.
     * @param RoomRepository $roomRepository
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(RoomRepository $roomRepository, SharedDataFetcher $sharedDataFetcher)
    {
        $this->roomRepository = $roomRepository;
        $this->sharedDataFetcher = $sharedDataFetcher;
    }


    public function getRawData(DataQueryInterface $dataQuery): array
    {
        return $this->roomRepository->fetchRawAllRoomsByRoomType();
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        return array_filter($data, function ($room) use ($roomTypeId){
            return $roomTypeId === $this->sharedDataFetcher->getRoomTypeIdOfRoomId((string)$room['_id']);
        });
    }

    public function getName(): string
    {
        return static::NAME;
    }


}