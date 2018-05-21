<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\PackageBundle\Document\SearchQueryTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SearchResultCacheItem
 * @package MBH\Bundle\BaseBundle\Document
 * @ODM\Document
 * @ODM\Index(keys={"begin"="asc","end"="asc","adults"="asc","tariff"="asc"})
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
     * @var string
     * @ODM\Field(type="string")
     */
    private $serializedSearchResults;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    private $byRoomTypes = true;

    /**
     * @return bool
     */
    public function isByRoomTypes(): ?bool
    {
        return $this->byRoomTypes;
    }

    /**
     * @param bool $byRoomTypes
     * @return SearchResultCacheItem
     */
    public function setByRoomTypes(bool $byRoomTypes): SearchResultCacheItem
    {
        $this->byRoomTypes = $byRoomTypes;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerializedSearchResults()
    {
        return $this->serializedSearchResults;
    }

    /**
     * @param string $serializedSearchResults
     * @return SearchResultCacheItem
     */
    public function setSerializedSearchResults(string $serializedSearchResults)
    {
        $this->serializedSearchResults = $serializedSearchResults;

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