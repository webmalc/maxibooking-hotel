<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use Symfony\Component\Cache\Simple\AbstractCache;

class DataFetcher implements DataFetcherInterface
{
    protected const REDIS_TIME_TTL = 300;

    /** @var AbstractCache */
    private $redis;

    /** @var DataRawFetcherInterface */
    private $rawFetcher;

    /**
     * AbstractDataFetcher constructor.
     * @param AbstractCache $redis
     * @param DataRawFetcherInterface $rawFetcher
     */
    public function __construct(AbstractCache $redis, DataRawFetcherInterface $rawFetcher)
    {
        $this->redis = $redis;
        $this->rawFetcher = $rawFetcher;
    }


    public function fetch(DataQueryInterface $dataQuery): array
    {
        $hash = $dataQuery->getSearchHash();
        if (!($data = $this->data[$hash] ?? null)) {
            if (!$this->redis->has($hash)) {
                $data = $this->fetchData($dataQuery);
                $this->redis->set($hash, $data, self::REDIS_TIME_TTL);
            } else {
                $data = $this->redis->get($hash);
            }

            $this->data[$hash] = $data;
        }

        return $this->getExactData($dataQuery, $data);
    }

    private function fetchData(DataQueryInterface $searchQuery): array
    {
        return $this->rawFetcher->getRawData($searchQuery);
    }

    private function getExactData(DataQueryInterface $searchQuery, array $data): array
    {
        $begin = $searchQuery->getBegin();
        $end = $searchQuery->getEnd();
        $tariffId = $searchQuery->getTariffId();
        $roomTypeId = $searchQuery->getRoomTypeId();

        return $this->rawFetcher->getExactData($begin, $end, $tariffId, $roomTypeId, $data);
    }

    public function getName(): string
    {
        return $this->rawFetcher->getName();
    }

    public function cleanMemoryData(string $hash): void
    {
        unset($this->data[$hash]);
    }

}