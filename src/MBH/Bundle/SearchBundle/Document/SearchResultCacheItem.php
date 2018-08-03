<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class SearchResultCacheItem
 * @package MBH\Bundle\SearchBundle\Document
 * @ODM\Document(collection="SearchResultCacheItems", repositoryClass="SearchResultCacheItemRepository")
 * @ODM\Index(keys={"begin"="asc","end"="asc","roomType"="asc","tariff"="asc", "adults"="asc", "children"="asc", "childrenAges"="asc"})
 */
class SearchResultCacheItem extends Base
{

    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="form.searchType.check_in_date_not_filled")
     * @Assert\Date()
     */
    private $begin;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="form.searchType.check_out_date_not_filled")
     * @Assert\Date()
     */
    private $end;

    /**
     * @var mixed
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    private $tariff;

    /**
     * @var mixed
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    private $roomType;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\NotNull(message="form.searchType.adults_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 12,
     *     minMessage = "form.searchType.adults_amount_less_zero"
     * )
     */
    private $adults;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\NotNull(message="orm.searchType.children_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
     */
    private $children;

    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    private $childrenAges = [];

    /**
     * @var array
     * @ODM\Field(type="hash")
     */
    private $serializedSearchResult;

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return SearchResultCacheItem
     */
    public function setBegin(\DateTime $begin): SearchResultCacheItem
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return SearchResultCacheItem
     */
    public function setEnd(\DateTime $end): SearchResultCacheItem
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param mixed $tariff
     * @return SearchResultCacheItem
     */
    public function setTariff($tariff): SearchResultCacheItem
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param mixed $roomType
     * @return SearchResultCacheItem
     */
    public function setRoomType($roomType): SearchResultCacheItem
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return SearchResultCacheItem
     */
    public function setAdults(int $adults): SearchResultCacheItem
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return SearchResultCacheItem
     */
    public function setChildren(int $children): SearchResultCacheItem
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildrenAges(): array
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     * @return SearchResultCacheItem
     */
    public function setChildrenAges(array $childrenAges): SearchResultCacheItem
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }

    /**
     * @return array
     */
    public function getSerializedSearchResult(): array
    {
        return $this->serializedSearchResult;
    }

    /**
     * @param array $serializedSearchResult
     * @return SearchResultCacheItem
     */
    public function setSerializedSearchResult(array $serializedSearchResult): SearchResultCacheItem
    {
        $this->serializedSearchResult = $serializedSearchResult;

        return $this;
    }




}