<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Validator\Constraints\Range;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Class SearchConditions
 * @package MBH\Bundle\SearchBundle\Document
 * @Range()
 * @ChildrenAgesSameAsChildren()
 * @ODM\Document(collection="SearchConditions")
 * @Gedmo\Loggable()
 */
class SearchConditions extends Base
{

    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $begin;

    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @var int
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
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
     */
    private $children;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $additionalBegin;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $additionalEnd;

    /**
     * @var array|int[]
     *
     */
    private $childrenAges = [];

    /**
     * @var ArrayCollection|Hotel[]
     */
    private $hotels;

    /**
     * @var  ArrayCollection|RoomType[]
     */
    private $roomTypes;

    /**
     * @var ArrayCollection|Tariff[]
     */
    private $tariffs;

    /** @var bool
     * @Assert\Type(type="bool")
     */
    private $isOnline = false;

    /** @var bool
     * @Assert\Type(type="bool")
     *
     */
    private $isIgnoreRestrictions = false;

    /** @var bool  */
    private $isForceBooking = false;

    /** @var string */
    private $searchHash = '';

    /**
     * SearchConditions constructor.
     */
    public function __construct()
    {
        $this->hotels = new ArrayCollection();
        $this->roomTypes = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
    }


    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return SearchConditions
     */
    public function setBegin(?\DateTime $begin): SearchConditions
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return SearchConditions
     */
    public function setEnd(?\DateTime $end): SearchConditions
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return SearchConditions
     */
    public function setAdults(int $adults): SearchConditions
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): ?int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return SearchConditions
     */
    public function setChildren(int $children): SearchConditions
    {
        $this->children = $children;

        return $this;
    }

    public function setHotels($hotels): SearchConditions
    {
        $this->hotels = $hotels;

        return $this;
    }

    public function getHotels(): ArrayCollection
    {
        return $this->hotels;
    }

    public function addRoomTypes(RoomType $roomType): SearchConditions
    {
        $this->roomTypes->add($roomType);

        return $this;
    }

    public function setRoomTypes($roomTypes): SearchConditions
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    public function getRoomTypes(): ArrayCollection
    {
        return $this->roomTypes;
    }

    /**
     * @return ArrayCollection|Tariff[]
     */
    public function getTariffs(): ArrayCollection
    {
        return $this->tariffs;
    }

    /** @return ArrayCollection|Tariff[] */
    public function getRestrictionTariffs(): ArrayCollection
    {
        $restrictionTariffs = [];
        foreach ($this->tariffs as $tariff) {
            if ($tariff->getParent() && $tariff->getChildOptions()->isInheritRooms()) {
                $restrictionTariffs[] = $tariff->getParent();
            }
        }

        return new ArrayCollection($restrictionTariffs);
    }

    /**
     * @param ArrayCollection|Tariff[] $tariffs
     * @return SearchConditions
     */
    public function setTariffs(ArrayCollection $tariffs): SearchConditions
    {
        $this->tariffs = $tariffs;

        return $this;
    }

    public function addTariff(Tariff $tariff): SearchConditions
    {
        $this->tariffs->add($tariff);

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalBegin(): ?int
    {
        if (null === $this->additionalBegin) {
            return 0;
        }

        return $this->additionalBegin;
    }

    /**
     * @param int $additionalBegin
     * @return SearchConditions
     */
    public function setAdditionalBegin(?int $additionalBegin): SearchConditions
    {
        $this->additionalBegin = $additionalBegin;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalEnd(): ?int
    {
        if (null === $this->additionalEnd && null !== $this->getAdditionalBegin()) {
            return $this->getAdditionalBegin();
        }

        return $this->additionalEnd;
    }

    /**
     * @param int $additionalEnd
     * @return SearchConditions
     */
    public function setAdditionalEnd(?int $additionalEnd): SearchConditions
    {
        $this->additionalEnd = $additionalEnd;

        return $this;
    }

    /**
     * @return array|int[]
     */
    public function getChildrenAges(): ?array
    {
        return $this->childrenAges;
    }

    /**
     * @param array|int[] $childrenAges
     * @return SearchConditions
     */
    public function setChildrenAges(?array $childrenAges)
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }

    public function addChildrenAge($age)
    {
        $this->childrenAges[] = $age;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    /**
     * @param bool $isOnline
     * @return SearchConditions
     */
    public function setIsOnline($isOnline): SearchConditions
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function isIgnoreRestrictoins(): bool
    {
        return $this->isIgnoreRestrictions;
    }

    public function setIgnoreRestrictions(): SearchConditions
    {
        $this->isIgnoreRestrictions = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForceBooking(): bool
    {
        return $this->isForceBooking;
    }

    /**
     * @param bool $isForceBooking
     * @return SearchConditions
     */
    public function setIsForceBooking(bool $isForceBooking): SearchConditions
    {
        $this->isForceBooking = $isForceBooking;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchHash(): string
    {
        return $this->searchHash;
    }

    /**
     * @param string $searchHash
     * @return SearchConditions
     */
    public function setSearchHash(string $searchHash): SearchConditions
    {
        $this->searchHash = $searchHash;

        return $this;
    }






}