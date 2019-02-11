<?php


namespace MBH\Bundle\SearchBundle\Lib\Result\Grouping;


use MBH\Bundle\SearchBundle\Lib\Result\Result;

class ErrorSplitGrouping  implements GroupingInterface
{

    /** @var GroupingInterface */
    private $grouper;

    /**
     * ErrorSplitGrouping constructor.
     * @param GroupingInterface $grouper
     */
    public function __construct(GroupingInterface $grouper)
    {
        $this->grouper = $grouper;
    }

    /** @param Result[] $searchResults
     * @return array
     */
    public function group(array $searchResults): array
    {
        $successResults = array_filter(
            $searchResults,
            function ($result) {
                return $result['status'] !== 'error';
            }
        );
        $errorResults = array_filter(
            $searchResults,
            function ($result) {
                return $result['status'] === 'error';
            }
        );

        return [
            'success' => $this->grouper->group($successResults),
            'errors' => $this->grouper->group($errorResults),
        ];

    }

}