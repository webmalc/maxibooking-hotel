<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use Symfony\Component\Cache\Simple\AbstractCache;

class DataFetcher implements DataFetcherInterface
{
    protected const REDIS_TIME_TTL = 120;

    /** @var AbstractCache */
    private $redis;

    /** @var DataRawFetcherInterface */
    private $rawFetcher;

    /** @var array */
    private $data;

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

        if ($data === null) {
            var_dump($dataQuery);
            $data = [];
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

    //** TODO: Временный костыль для очистки памяти пока не придет что то вменяемое. */
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