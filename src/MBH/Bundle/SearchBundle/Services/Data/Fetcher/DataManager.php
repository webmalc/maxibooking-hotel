<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use MBH\Bundle\SearchBundle\Lib\Events\SearchEvent;
use MBH\Bundle\SearchBundle\Lib\Exceptions\DataFetchQueryException;
use Monolog\Logger;
use Predis\Client;
use Psr\Log\LoggerInterface;


class DataManager
{
    /** @var DataFetcherInterface[] */
    private $fetchersMap = [];

    /** @var Client */
    private $client;

    /**
     * DataManager constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }


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

    public function cleanMemoryData(): void
    {
        $hashes = $this->client->smembers(SearchEvent::SEARCH_ASYNC_END);
        if (count($hashes)) {
            foreach ($hashes as $hashKey) {
                $hash = $this->client->get($hashKey);
                    if ($hash) {
                        foreach ($this->fetchersMap as $fetcher) {
                            $fetcher->cleanMemoryData($hash);
                        }
                    }
            }
        }

    }

}