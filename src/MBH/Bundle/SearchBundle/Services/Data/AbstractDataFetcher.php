<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;
use Symfony\Component\Cache\Simple\AbstractCache;

abstract class AbstractDataFetcher implements DataFetcherInterface
{
    /** @var DataHolderInterface */
    private $holder;

    /** @var AbstractCache */
    private $cache;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher, AbstractCache $cache)
    {
        $this->holder = $holder;
        $this->cache = $cache;
    }

    public function fetchNecessaryDataSet(DataFetchQueryInterface $fetchQuery): array
    {
        $result = $this->holder->get($fetchQuery);
        if (null === $result) {
            $data = $this->getData($fetchQuery);
            $this->holder->set($fetchQuery, $data);
            $result = $this->holder->get($fetchQuery);
        }

        return $result;

    }

    private function getData(DataFetchQueryInterface $fetchQuery): array
    {
        $hash = $fetchQuery->getHash();
        if ($this->cache->has($hash)) {
            return $this->cache->get($hash);
        }
        $data = $this->fetchData($fetchQuery);
        if (!$this->cache->has($hash)) {
            $this->cache->set($hash, $data);
        }

        return $data;

    }

    abstract protected function fetchData(DataFetchQueryInterface $fetchQuery): array;

}