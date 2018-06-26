<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface DataFetcherInterface
{
    public function fetchNecessaryDataSet(SearchQuery $searchQuery): array;
}