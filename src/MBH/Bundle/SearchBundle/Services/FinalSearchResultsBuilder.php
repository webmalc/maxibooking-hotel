<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Exceptions\FilterResultException;
use MBH\Bundle\SearchBundle\Lib\Result\Grouping\GroupingFactory;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use MBH\Bundle\SearchBundle\Services\Search\AsyncResultStores\SearchConditionsInterface;

class FinalSearchResultsBuilder
{
    /** @var array */
    private $results;

    /** @var bool */
    private $filterLevel = true;

    /** @var string */
    private $grouping;

    /** @var string */
    private $isCreateJson;

    /** @var ResultSerializer */
    private $serializer;

    /** @var bool */
    private $isCreateAnswer = false;

    /** @var string */
    private $searchHashConditions;

    /** @var ErrorResultFilter */
    private $errorFilter;

    /**
     * SearchResultsFinalHandler constructor.
     * @param ResultSerializer $serializer
     * @param ErrorResultFilter $errorFilter
     */
    public function __construct(ResultSerializer $serializer, ErrorResultFilter $errorFilter)
    {
        $this->serializer = $serializer;
        $this->errorFilter = $errorFilter;
    }

    public function set($results): self
    {
        if (!\is_iterable($results)) {
            $this->addResult($results);
        } else {
            foreach ($results as $result) {
                $this->addResult($result);
            }
        }

        return $this;
    }

    public function setSearchHashConditions(SearchConditionsInterface $conditions): void
    {
        $this->searchHashConditions = $conditions->getSearchHash();
    }


    public function addResult($result): self
    {
        if ($result instanceof Result) {
            $result = $this->serializer->serialize($result, 'array');
        }
        $this->results[] = $result;

        return $this;
    }

    public function errorFilter(int $level = 0): self
    {
        $this->filterLevel = $level;

        return $this;
    }

    public function setGrouping(?string $grouping): self
    {
        $this->grouping = $grouping;

        return $this;
    }

    public function createJson(bool $isCreate = true): self
    {
        $this->isCreateJson = $isCreate;

        return $this;
    }

    public function createAnswer(bool $isCreateAnswer): self {
        $this->isCreateAnswer = $isCreateAnswer;

        return $this;
    }


    /**
     * @return mixed
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function getResults()
    {
        $results = $this->results ?? [];

        if (\count($results)) {
            $results = array_filter($results, [$this, 'filterError']);

        }
        $results = array_values($results);
        $groupingFactory = new GroupingFactory();
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $grouping = $groupingFactory->createGrouping($this->grouping);
        $results = $grouping->group($results);

        if ($this->isCreateAnswer) {

            $results = [
                'results' => \count($results) ? $results : [],
                'conditionHash' => $this->searchHashConditions
            ];
        }

        if ($this->isCreateJson) {
            $results = $this->serializer->encodeArrayToJson($results);

        }
        $this->unsetResults();

        return $results;
    }

    private function filterError(array $result): bool
    {
        try {
            $this->errorFilter->arrayFilter($result, $this->filterLevel);

            return true;
        } catch (FilterResultException $e) {
            return false;
        }
    }

    private function unsetResults(): void
    {
        $this->results = null;
    }

}