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
     * RoomRawFetcher constructor.
     * @param RoomRepository $roomRepository
     * @param SharedDataFetcher $sharedDataFetcher
     */
    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }


    public function getRawData(ExtendedDataQueryInterface $dataQuery): array
    {
        return $this->roomRepository->fetchRawAllRoomsByRoomType([], true);
    }

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array
    {
        return $data[$roomTypeId] ?? [];
    }

    public function getName(): string
    {
        return static::NAME;
    }


}