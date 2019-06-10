<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use MBH\Bundle\SearchBundle\Lib\Exceptions\AsyncDataFetcherException;
use Monolog\Logger;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Simple\AbstractCache;

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

    /** @var AbstractCache */
    private $redis;

    /** @var DataRawFetcherInterface */
    private $rawFetcher;

    /** @var array */
    private $data;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * AbstractDataFetcher constructor.
     * @param AbstractCache $redis
     * @param DataRawFetcherInterface $rawFetcher
     * @param Logger $logger
     */
    public function __construct(AbstractCache $redis, DataRawFetcherInterface $rawFetcher,  Logger $logger)
    {
        $this->redis = $redis;
        $this->rawFetcher = $rawFetcher;
        $this->logger = $logger;
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

        if (!($data = $this->data[$hash] ?? null)) {
            if (!$this->redis->has($hash)) {
                $data = $this->fetchData($dataQuery);
                $this->redis->set($hash, $data, self::REDIS_TIME_TTL);
                if ($data === null) {
                    $this->logger->debug(
                        sprintf('Fetched data hashed %s from %s is null inside %s fetcher', $hash, 'fetchData method', $this->getName())
                    );
                }
            } else {
                $data = $this->redis->get($hash);
                $this->logger->debug(
                    sprintf('Fetched data hashed %s from %s is null inside %s fetcher', $hash, 'REDIS', $this->getName())
                );
            }
            $this->data[$hash] = $data;
        } else {
            $this->logger->debug(sprintf('Data hashed %s from memory inside %s fetcher', $hash, $this->getName()));
        }

        if ($data === null) {
            $message = sprintf('Data hashed %s is null, but at least empty array expected in %s fetcher', $hash, $this->getName());
            $this->logger->critical($message);
            throw new AsyncDataFetcherException($message);
        }

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

        if ($this->redis->has($hash)) {
            $this->redis->deleteItem($hash);
        }

    }

}