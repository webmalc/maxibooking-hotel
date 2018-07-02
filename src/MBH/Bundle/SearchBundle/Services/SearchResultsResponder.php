<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

class SearchResultsResponder
{
    /**
     * @param SearchResult[] $searchResults
     * @return array
     */
    public function handleResults(array $searchResults): array
    {
        $results = [];

        foreach ($searchResults as $searchResult) {
            /** @var SearchResult $searchResult */
            $results[] = Result::createInstance($searchResult);
        }

        return $results;
    }
}