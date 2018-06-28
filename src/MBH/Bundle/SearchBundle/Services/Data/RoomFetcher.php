<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

class RoomFetcher extends AbstractDataFetcher
{
    /** @var RoomRepository */
    private $roomRepository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
        parent::__construct($holder, $sharedDataFetcher);
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->roomRepository->fetchRawAllRoomsByRoomType([], true);
    }

}