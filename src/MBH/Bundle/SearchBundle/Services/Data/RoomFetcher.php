<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use Symfony\Component\Cache\Simple\AbstractCache;

class RoomFetcher extends AbstractDataFetcher
{
    /** @var RoomRepository */
    private $roomRepository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RoomRepository $roomRepository, AbstractCache $cache)
    {
        $this->roomRepository = $roomRepository;
        parent::__construct($holder, $sharedDataFetcher, $cache);
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->roomRepository->fetchRawAllRoomsByRoomType([], true);
    }

}