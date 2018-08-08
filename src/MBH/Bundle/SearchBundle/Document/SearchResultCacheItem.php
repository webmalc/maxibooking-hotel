<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\SearchBundle\Lib\Result\Result;
use MBH\Bundle\SearchBundle\Services\Data\Serializers\ResultSerializer;
use Symfony\Component\Serializer\SerializerInterface;
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
    public function getSerializedSearchResult(): string
    {
        return $this->serializedSearchResult;
    }

    /**
     * @param string $serializedSearchResult
     * @return SearchResultCacheItem
     */
    public function setSerializedSearchResult(string $serializedSearchResult): SearchResultCacheItem
    {
        $this->serializedSearchResult = $serializedSearchResult;

        return $this;
    }

    public static function createInstance(Result $result, ResultSerializer $serializer = null): SearchResultCacheItem
    {
        $cacheItem = new self();
        $roomTypeId = $result->getResultRoomType()->getId();
        $tariffId = $result->getResultTariff()->getId();
        $resultConditions = $result->getResultConditions();
        $adults = $resultConditions->getAdults();
        $children = $resultConditions->getChildren();
        $childrenAges = $resultConditions->getChildrenAges();
        if (null !== $serializer) {
            $serializedResult = $serializer->serialize($result);
        } else {
            $serializedResult = json_encode($result, JSON_UNESCAPED_UNICODE);
        }

        $cacheItem
            ->setBegin($result->getBegin())
            ->setEnd($result->getEnd())
            ->setRoomTypeId($roomTypeId)
            ->setTariffId($tariffId)
            ->setAdults($adults)
            ->setChildren($children)
            ->setChildrenAges($childrenAges)
            ->setSerializedSearchResult($serializedResult);

        return $cacheItem;
    }


}