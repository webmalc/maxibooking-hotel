<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


interface DataFetcherInterface
{
    public function fetch(DataQueryInterface $dataQuery): array;

    public function cleanMemoryData(string $hash): void;

    public function getName(): string;
}