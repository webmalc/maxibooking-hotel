<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


use MBH\Bundle\SearchBundle\Lib\Data\DataFetchQueryInterface;

interface DataHolderInterface
{
    public function get(DataFetchQueryInterface $fetchQuery): ?array;

    public function set(DataFetchQueryInterface $fetchQuery, array $data);
}