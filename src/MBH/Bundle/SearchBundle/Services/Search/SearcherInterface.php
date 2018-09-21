<?php


namespace MBH\Bundle\SearchBundle\Services\Search;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface SearcherInterface
{
    public function search(SearchQuery $searchQuery);

}