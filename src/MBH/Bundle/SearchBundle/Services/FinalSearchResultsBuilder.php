<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Result\Grouping\GroupingFactory;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class FinalSearchResultsBuilder
{
    /** @var array */
    private $results;

    /** @var bool */
    private $isFilterError = true;

    /** @var string */
    private $grouping;

    /** @var string */
    private $isCreateJson;

    /** @var ResultSerializer */
    private $serializer;

    /** @var bool */
    private $isCreateAnswer = false;

    /**
     * SearchResultsFinalHandler constructor.
     * @param ResultSerializer $serializer
     */
    public function __construct(ResultSerializer $serializer)
    {
        $this->serializer = $serializer;
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

    public function addResult($result): self
    {
        if ($result instanceof Result) {
            $result = $this->serializer->serialize($result, 'array');
        }
        $this->results[] = $result;

        return $this;
    }

    public function hideError(bool $hide = true): self
    {
        $this->isFilterError = $hide;

        return $this;
    }

    public function setGrouping(string $grouping = null): self
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
        $results = $this->results;

        if ($this->isFilterError && \count($results)) {
            $results = array_filter($results, [$this, 'filterError']);
        }

        if ($this->grouping && \count($results)) {
            $groupingFactory = new GroupingFactory();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $grouping = $groupingFactory->createGrouping($this->grouping);
            $results = $grouping->group($results);
        }

        if ($this->isCreateAnswer) {
            $results = [
                'results' => $results
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
        return $result['status'] === 'ok';
    }

    public function unsetResults(): void
    {
        $this->results = null;
    }

}