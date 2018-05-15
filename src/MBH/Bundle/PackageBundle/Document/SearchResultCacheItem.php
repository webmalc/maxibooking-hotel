<?php

namespace MBH\Bundle\BaseBundle\Document;

use MBH\Bundle\PackageBundle\Document\SearchQueryTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SearchResultCacheItem
 * @package MBH\Bundle\BaseBundle\Document
 * @ODM\Document
 */
class SearchResultCacheItem
{
    use SearchQueryTrait;

    /**
     * @var string
     * @ODM\Id
     */
    private $id;

    /**
     * @var
     * @ODM\Field(type="collection")
     */
    private $searchResults;

    /**
     * @return array
     */
    public function getSearchResults()
    {
        return $this->searchResults;
    }

    /**
     * @param array $searchResults
     * @return SearchResultCacheItem
     */
    public function setSearchResults($searchResults)
    {
        $this->searchResults = $searchResults;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return SearchResultCacheItem
     */
    public function setId(string $id): SearchResultCacheItem
    {
        $this->id = $id;

        return $this;
    }
}