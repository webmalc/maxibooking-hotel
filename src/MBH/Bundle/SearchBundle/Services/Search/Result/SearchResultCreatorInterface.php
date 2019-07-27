<?php


namespace MBH\Bundle\SearchBundle\Services\Search\Result;


use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

interface SearchResultCreatorInterface
{
    public function createResult(SearchQuery $searchQuery, array $prices, int $roomAvailableAmount): ResultInterface;

    public function createErrorResult(SearchQuery $searchQuery, SearchException $e): ResultInterface;
}