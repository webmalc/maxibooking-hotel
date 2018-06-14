<?php


namespace MBH\Bundle\SearchBundle\Lib;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;

class ErrorSearchResult
{
    public static function createErrorResult(SearchException $exception): SearchResult
    {
        $result = new SearchResult();
        $result
            ->setStatus('error')
            ->setError($exception->getMessage())
            ;

        return $result;
    }
}