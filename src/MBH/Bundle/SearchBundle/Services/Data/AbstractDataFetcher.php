<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

abstract class AbstractDataFetcher implements DataFetcherInterface
{
    /** @var DataHolderInterface */
    private $holder;

    public function __construct(DataHolderInterface $holder, SharedDataFetcherInterface $sharedDataFetcher)
    {
        $this->holder = $holder;
    }

    public function fetchNecessaryDataSet(DataFetchQueryInterface $fetchQuery): array
    {
        $result = $this->holder->get($fetchQuery);
        if (null === $result) {
            $result = $this->fetchData($fetchQuery);
        }
        $this->holder->set($fetchQuery, $result);

        return $this->holder->get($fetchQuery);

    }

    abstract protected function fetchData(DataFetchQueryInterface $fetchQuery): array;

}