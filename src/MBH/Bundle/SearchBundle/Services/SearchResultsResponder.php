<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Result\Grouping\GroupingFactory;
use MBH\Bundle\SearchBundle\Lib\Result\Result;

class SearchResultsResponder
{

    /**
     * @param Result[] $results
     * @param null|string $grouping
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function handleResults(array $results, ?string $grouping = null): array
    {
        if ($grouping) {
            $groupingFactory = new GroupingFactory();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $grouping = $groupingFactory->createGrouping($grouping);
            $results = $grouping->group($results);
        }

        return $results;
    }

    public function handleAsyncResults(array $results, ?string $goruping = null): array
    {
        return $this->handleResults(array_values($results));
    }
}