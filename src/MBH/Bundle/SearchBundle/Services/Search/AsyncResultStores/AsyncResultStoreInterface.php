<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Result\ResultCacheablesInterface;

interface AsyncResultStoreInterface
{
    public function store($result):  void;

    public function receive(SearchConditions $conditions);
}