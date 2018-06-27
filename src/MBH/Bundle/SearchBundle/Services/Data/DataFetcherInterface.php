<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

interface DataFetcherInterface
{
    public function fetchNecessaryDataSet(DataFetchQueryInterface $searchQuery): array;
}