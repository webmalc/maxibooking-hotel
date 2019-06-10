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

    /** @var LoggerInterface */
    private $logger;

    /**
     * DataManager constructor.
     * @param Client $client
     * @param Logger $logger
     */
    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
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
                            $this->logger->debug(
                                sprintf('CleanMemory hash= %s in fetcher = %s', $hash, $fetcher->getName())
                            );
                            $fetcher->cleanMemoryData($hash);
                        }
                    }
            }
        }

    }

}