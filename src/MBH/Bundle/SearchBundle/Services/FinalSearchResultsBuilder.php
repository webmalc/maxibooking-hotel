<?php


namespace MBH\Bundle\SearchBundle\Services;


use MBH\Bundle\SearchBundle\Lib\Result\Grouping\GroupingFactory;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use Symfony\Component\Serializer\SerializerInterface;

class FinalSearchResultsBuilder
{
    /** @var array */
    private $results;
    /** @var bool */
    private $isFilterError = true;
    /** @var string */
    private $grouping;

    /** @var string */
    private $serializeType;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * SearchResultsFinalHandler constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function set($results): self
    {
        if (!\is_array($results)) {
            $this->addResult($results);
        } else {
            foreach ($results as $result) {
                $this->addResult($result);
            }
        }

        return $this;
    }

    public function addResult(Result $result): self
    {
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

    public function serialize(?string $serialize = 'json'): self
    {
        $this->serializeType = $serialize;

        return $this;
    }


    /**
     * @return mixed
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\GroupingFactoryException
     */
    public function getResults()
    {
        $results = $this->results;
        if ($this->isFilterError) {
            $results = array_filter($results, [$this, 'filterError']);
        }

        if ($this->grouping) {
            $groupingFactory = new GroupingFactory();
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $grouping = $groupingFactory->createGrouping($this->grouping);
            $results = $grouping->group($results);
        }

        if ($this->serializeType) {
            $results = $this->serializer
                ->serialize(
                    $results,
                    $this->serializeType,
                    [
                        'json_encode_options' => JSON_UNESCAPED_UNICODE
                    ]
                );
        }
        $this->unsetResults();

        return $results;
    }

    private function filterError(Result $result): bool
    {
        return $result->getStatus() === 'ok';
    }

    public function unsetResults(): void
    {
        $this->results = null;
    }

}