<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

abstract class AbstractDataFetcher implements DataFetcherInterface, SharedDataFetcherInterface
{
    /** @var DataHolderInterface */
    private $holder;

    /** @var SharedDataFetcherInterface */
    private $sharedFetcher;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher)
    {
        $this->holder = $holder;
        $this->sharedFetcher = $sharedDataFetcher;
    }

    /**
     * @param string $tariffId
     * @return Tariff
     * @throws SharedFetcherException
     */
    public function getFetchedTariff(string $tariffId): Tariff
    {
        return $this->sharedFetcher->getFetchedTariff($tariffId);
    }

    /**
     * @param string $roomTypeId
     * @return RoomType
     * @throws SharedFetcherException
     */
    public function getFetchedRoomType(string $roomTypeId): RoomType
    {
        return $this->sharedFetcher->getFetchedRoomType($roomTypeId);
    }

    public function fetchNecessaryDataSet(SearchQuery $searchQuery): array
    {
        $hash = $searchQuery->getSearchHash();
        $result = $this->holder->get($hash);
        if (null === $result) {
            $result = $this->fetchData($searchQuery);
        }
        $this->holder->set($hash, $result);

        return $result;

    }


    abstract protected function fetchData(SearchQuery $searchQuery): array;

}