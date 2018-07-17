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
        $results = array_filter($results, function ($result) {
            /** @var Result $result */
            return $result->getStatus() === 'ok';
        });
        if ($grouping) {
            $groupingFactory = new GroupingFactory();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $grouping = $groupingFactory->createGrouping($grouping);
            $results = $grouping->group($results);
        }

        return $results;
    }

}