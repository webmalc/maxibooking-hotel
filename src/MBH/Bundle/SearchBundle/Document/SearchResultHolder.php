<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchResultHolder
 * @package MBH\Bundle\SearchBundle\Document
 * @ODM\Document(collection="SearchResultHolders")
 */
class SearchResultHolder extends Base
{
    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    private $expectedResultsCount;

    /**
     * @var string
     * @Assert\Choice({"sync", "async"})
     * @Assert\Type(type="string")
     */
    private $type;

    /**
     * @var ArrayCollection|SearchResult[]
     */
    private $searchResults;

    /**
     * @var ArrayCollection|SearchResult[]
     */
    private $takenSearchResults;

    /**
     * @var SearchConditions
     */
    private $searchConditions;

    public function __construct()
    {
        $this->searchResults = new ArrayCollection();
        $this->takenSearchResults = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getExpectedResultsCount(): int
    {
        return $this->expectedResultsCount;
    }

    /**
     * @param int $expectedResultsCount
     * @return SearchResultHolder
     */
    public function setExpectedResultsCount(int $expectedResultsCount): SearchResultHolder
    {
        $this->expectedResultsCount = $expectedResultsCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SearchResultHolder
     */
    public function setType(string $type): SearchResultHolder
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return ArrayCollection|SearchResult[]
     */
    public function getSearchResults(): ArrayCollection
    {
        return $this->searchResults;
    }

    /**
     * @param SearchResult $searchResult
     * @return SearchResultHolder
     */
    public function addSearchResult(SearchResult $searchResult): SearchResultHolder
    {
        $this->searchResults->add($searchResult);

        return $this;
    }

    /**
     * @return ArrayCollection|SearchResult[]
     */
    public function getTakenSearchResults(): ArrayCollection
    {
        return $this->takenSearchResults;
    }

    /**
     * @param SearchResult $takenSearchResult
     * @return SearchResultHolder
     */
    public function addTakenSearchResults(SearchResult $takenSearchResult): SearchResultHolder
    {
        $this->takenSearchResults->add($takenSearchResult);

        return $this;
    }

    /**
     * @return SearchConditions
     */
    public function getSearchConditions(): ?SearchConditions
    {
        return $this->searchConditions;
    }

    /**
     * @param SearchConditions $searchConditions
     * @return SearchResultHolder
     */
    public function setSearchConditions(SearchConditions $searchConditions): SearchResultHolder
    {
        $this->searchConditions = $searchConditions;

        return $this;
    }



    public function getAsyncResults(): ?array
    {
        if ($this->takenSearchResults->count() === $this->expectedResultsCount) {
            return null;
        }

        $results = array_diff($this->getSearchResults()->toArray(), $this->getTakenSearchResults()->toArray());
        if (\count($results)) {
            foreach ($results as $result) {
                $this->addTakenSearchResults($result);
            }
        }

        return $results;
    }


}