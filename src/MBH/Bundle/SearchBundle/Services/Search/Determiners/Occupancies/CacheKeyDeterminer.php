<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Data\SharedDataFetcher;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface;

class CacheKeyDeterminer implements CacheKeyOccupancyDetermineInterface
{
    /** @var OccupancyDeterminerFactory */
    private $factory;

    /** @var SharedDataFetcher */
    private $sharedFetcher;

    /**
     * CacheKeyDeterminer constructor.
     * @param OccupancyDeterminerFactory $factory
     * @param SharedDataFetcher $dataFetcher
     */
    public function __construct(OccupancyDeterminerFactory $factory, SharedDataFetcher $dataFetcher)
    {
        $this->factory = $factory;
        $this->sharedFetcher = $dataFetcher;
    }


    /**
     * @param SearchQuery $searchQuery
     * @param string $type
     * @return \MBH\Bundle\SearchBundle\Services\Search\Determiners\OccupancyInterface
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\OccupancyDeterminerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function determine(SearchQuery $searchQuery, string $type): OccupancyInterface
    {
        $determiner = $this->factory->create($type);
        $occupancy = Occupancy::createInstanceBySearchQuery($searchQuery);
        $tariff = $this->sharedFetcher->getFetchedTariff($searchQuery->getTariffId());
        $roomType = $this->sharedFetcher->getFetchedRoomType($searchQuery->getRoomTypeId());

        return $determiner->determine($occupancy, $tariff, $roomType);
    }

}