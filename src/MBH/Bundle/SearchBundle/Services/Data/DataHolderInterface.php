<?php


namespace MBH\Bundle\SearchBundle\Services\Data;


interface DataHolderInterface
{
    public function get(string $hash): ?array;

    public function set(string $hash, array $data);
}