<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;

class DataManager
{
    /** @var DataFetcherInterface[] */
    private $fetchersMap = [];

    public function addFetcher(DataFetcherInterface $fetcher): void
    {
        $this->fetchersMap[$fetcher->getName()] = $fetcher;
    }

    public function fetchData(DataQueryInterface $searchQuery, string $type): array
    {
        $fetcher = $this->fetchersMap[$type] ?? null;
        if (null === $fetcher) {
            throw new DataFetchQueryException(sprintf('Error when try to get %s fetcher', $type));
        }

        return $fetcher->fetch($searchQuery);
    }
}