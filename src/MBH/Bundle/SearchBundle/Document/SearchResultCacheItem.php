<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultCacheException;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Lib\Result\ResultInterface;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class SearchResultCacheItem
 * @package MBH\Bundle\SearchBundle\Document
 * @ODM\Document(collection="SearchResultCacheItems", repositoryClass="SearchResultCacheItemRepository")
 * @ODM\Index(keys={"begin"="asc","end"="asc","roomType"="asc","tariff"="asc", "adults"="asc", "children"="asc", "childrenAges"="asc"})
 */
class SearchResultCacheItem
{
    /**
     * @var string
     * @ODM\Id
     */
    private $id;

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
     * @var string
     * @ODM\Field(type="string")
     */
    private $tariffId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $roomTypeId;

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
     * @var int|null
     * @ODM\Field(type="integer")
     * @Assert\Range(
     *     min = 0,
     *     max = 512
     * )
     */
    private $errorType;

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
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Assert\NotNull()
     */
    private $cacheResultKey;

    public function setId($id): SearchResultCacheItem
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

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
    public function getTariffId()
    {
        return $this->tariffId;
    }

    /**
     * @param string $tariffId
     * @return SearchResultCacheItem
     */
    public function setTariffId(string $tariffId): SearchResultCacheItem
    {
        $this->tariffId = $tariffId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoomTypeId(): string
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
     * @return string
     */
    public function getCacheResultKey(): string
    {
        return $this->cacheResultKey;
    }

    /**
     * @param string $cacheResultKey
     * @return SearchResultCacheItem
     */
    public function setCacheResultKey(string $cacheResultKey): SearchResultCacheItem
    {
        $this->cacheResultKey = $cacheResultKey;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getErrorType(): ?int
    {
        return $this->errorType;
    }

    /**
     * @param int|null $errorType
     * @return SearchResultCacheItem
     */
    public function setErrorType(?int $errorType): SearchResultCacheItem
    {
        $this->errorType = $errorType;

        return $this;
    }

    /**
     * @param Result $result
     * @param SearchQuery $searchQuery
     * @return SearchResultCacheItem
     * @throws SearchResultCacheException
     */
    public static function createInstance(ResultInterface $result): SearchResultCacheItem
    {
        $cacheItem = new self();
        $roomTypeId = $result->getRoomType();
        $tariffId = $result->getTariff();
        $adults = $result->getAdults();
        $children = $result->getChildren();
        $childrenAges = $result->getChildrenAges();
        $cacheItem
            ->setBegin($result->getBegin())
            ->setEnd($result->getEnd())
            ->setRoomTypeId($roomTypeId)
            ->setTariffId($tariffId)
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges)
            ->setErrorType($result->getErrorType())
        ;

        return $cacheItem;
    }


}