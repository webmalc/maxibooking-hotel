<?php


namespace MBH\Bundle\SearchBundle\Services\Data\Fetcher;


use DateTime;

interface DataRawFetcherInterface
{
    public function getRawData(ExtendedDataQueryInterface $dataQuery): array;

    public function getExactData(DateTime $begin, DateTime $end, string $tariffId, string $roomTypeId, array $data): array;

    public function getName(): string;
}