<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Document\SearchResult;
use MBH\Bundle\SearchBundle\Lib\Result\Grouping\GroupingFactory;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

class SearchResultsResponder
{

    /**
     * @param SearchResult[] $searchResults
     * @param null|string $grouping
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function handleResults(array $searchResults, ?string $grouping = null): array
    {
        $results = [];

        foreach ($searchResults as $searchResult) {
            /** @var SearchResult $searchResult */
            $results[] = Result::createInstance($searchResult);

        }

        if ($grouping) {
            $groupingFactory = new GroupingFactory();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $grouping = $groupingFactory->createGrouping($grouping);
            $results = $grouping->group($results);
        }


        return $results;
    }
}