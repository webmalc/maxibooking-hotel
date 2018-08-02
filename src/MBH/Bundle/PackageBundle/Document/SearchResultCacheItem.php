<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SearchResultCacheItem
 * @package MBH\Bundle\BaseBundle\Document
 * @ODM\Document
 * @ODM\Index(keys={"begin"="asc","end"="asc","roomTypeId"="asc","tariff"="asc"})
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
    private $serializedSearchResult;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $roomTypeId;

    /**
     * @return string
     */
    public function getSerializedSearchResult()
    {
        return $this->serializedSearchResult;
    }

    /**
     * @param string|null $serializedSearchResult
     * @return SearchResultCacheItem
     */
    public function setSerializedSearchResult(?string $serializedSearchResult)
    {
        $this->serializedSearchResult = $serializedSearchResult;

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

    /**
     * @return string
     */
    public function getRoomTypeId(): ?string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return SearchResultCacheItem
     */
    public function setRoomTypeId(string $roomTypeId): SearchResultCacheItem
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }
}