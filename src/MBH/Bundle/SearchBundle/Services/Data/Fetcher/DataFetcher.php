<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncDataFetcherException;
use Predis\Client;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class DataFetcher
 * @package MBH\Bundle\SearchBundle\Services\Data\Fetcher
 */
class DataFetcher implements DataFetcherInterface
{
    /**
     * @var int
     */
    protected const REDIS_TIME_TTL = 120;

    /** @var DataRawFetcherInterface */
    private $rawFetcher;

    /** @var array */
    private $data;

    /**
     * @var Client
     */
    private $client;

    /**
     * AbstractDataFetcher constructor.
     * @param Client $client
     * @param DataRawFetcherInterface $rawFetcher
     */
    public function __construct(Client $client, DataRawFetcherInterface $rawFetcher)
    {
        $this->client = $client;
        $this->rawFetcher = $rawFetcher;
    }


    /**
     * @param DataQueryInterface $dataQuery
     * @return array
     * @throws AsyncDataFetcherException
     * @throws InvalidArgumentException
     */
    public function fetch(DataQueryInterface $dataQuery): array
    {
        $hash = $dataQuery->getSearchHash();
        $key = $this->createKey($hash);
        if (!($data = $this->data[$hash] ?? null)) {
            if (!$this->client->exists($key)) {
                $data = $this->fetchData($dataQuery);
                /** @noinspection NotOptimalIfConditionsInspection */
                if (!$this->client->exists($key)) {
                    $this->client->transaction()->set($key, serialize($data), 'EX', static::REDIS_TIME_TTL)->exec();
                }

            } else {

                $data = unserialize($this->client->get($key), ['allowed_classes' => true]);

            }
            $this->data[$hash] = $data;
        }

//        if ($data === null) {
//            $message = sprintf('%s. %s Received from all resources are NULL. PID %u', $name, $hash, getmypid());
//            throw new AsyncDataFetcherException($message);
//        }

        return $this->getExactData($dataQuery, $data);
    }

    /**
     * @param DataQueryInterface $searchQuery
     * @return array
     */
    private function fetchData(DataQueryInterface $searchQuery): array
    {
        return $this->rawFetcher->getRawData($searchQuery);
    }

    /**
     * @param DataQueryInterface $searchQuery
     * @param array $data
     * @return array
     */
    private function getExactData(DataQueryInterface $searchQuery, array $data): array
    {
        $begin = $searchQuery->getBegin();
        $end = $searchQuery->getEnd();
        $tariffId = $searchQuery->getTariffId();
        $roomTypeId = $searchQuery->getRoomTypeId();

        return $this->rawFetcher->getExactData($begin, $end, $tariffId, $roomTypeId, $data);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->rawFetcher->getName();
    }

    //** TODO: Временный костыль для очистки памяти пока не придет что то вменяемое. */

    /**
     * @param string $hash
     */
    public function cleanMemoryData(string $hash): void
    {
        if (isset($this->data[$hash])) {
            unset($this->data[$hash]);
        }

        if ($this->client->exists($key = $this->createKey($hash))) {
            $this->client->del([$key]);
        }

    }

    private function createKey(string $hash): string
    {
        return $this->getName().$hash;
    }

}