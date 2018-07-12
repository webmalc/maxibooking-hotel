<?php


namespace MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

interface AsyncResultStoreInterface
{
    public function store(Result $searchResult):  void;
}