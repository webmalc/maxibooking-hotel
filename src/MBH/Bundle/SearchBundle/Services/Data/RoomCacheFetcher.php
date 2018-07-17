<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use Symfony\Component\Cache\Simple\AbstractCache;

class RoomCacheFetcher extends AbstractDataFetcher
{
    /** @var RoomCacheRepository */
    private $roomCacheRepository;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, RoomCacheRepository $roomCacheRepository, AbstractCache $cache)
    {
        $this->roomCacheRepository = $roomCacheRepository;
        parent::__construct($holder, $sharedDataFetcher, $cache);
    }


    protected function fetchData(DataFetchQueryInterface $fetchQuery): array
    {
        return $this->roomCacheRepository->fetchRaw($fetchQuery->getMaxBegin(), $fetchQuery->getMaxEnd());
    }

}