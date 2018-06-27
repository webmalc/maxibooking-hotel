<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

class RoomCacheFetcher extends AbstractDataFetcher
{
    /** @var RoomCacheRepository */
    private $roomCacheRepository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RoomCacheRepository $roomCacheRepository)
    {
        $this->roomCacheRepository = $roomCacheRepository;
        parent::__construct($holder, $sharedDataFetcher);
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->roomCacheRepository->fetchRaw($fetchQuery->getMaxBegin(), $fetchQuery->getMaxEnd());
    }

}