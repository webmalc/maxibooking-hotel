<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Validator\Constraints\Range;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Services\Cache\ErrorFilters\ErrorResultFilter;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Class SearchConditions
 * @package MBH\Bundle\SearchBundle\Document
 * @Range()
 * @ChildrenAgesSameAsChildren()
 * @ODM\Document(collection="SearchConditions", repositoryClass="SearchConditionsRepository")
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
     * @ODM\Field(type="date")
     */
    private $begin;

    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @Assert\NotNull()
     * @ODM\Field(type="date")
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
     * @ODM\Field(type="int")
     */
    private $adults;

    /**
     * @var int
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
     * @ODM\Field(type="int")
     */
    private $children;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=20
     * )
     * @ODM\Field(type="int")
     */
    private $additionalBegin;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=20
     * )
     * @ODM\Field(type="int")
     */
    private $additionalEnd;

    /**
     * @var array|int[]
     * @Assert\Collection()
     * @ODM\Field(type="collection")
     *
     */
    private $childrenAges = [];

    /**
     * @var ArrayCollection|Hotel[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    private $hotels;

    /**
     * @var  ArrayCollection|RoomType[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    private $roomTypes;

    /**
     * @var ArrayCollection|Tariff[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    private $tariffs;

    /**
     * @var Special
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Special")
     */
    private $special;

    /** @var bool
     * @Assert\Type(type="bool")
     */
    private $isOnline = false;

    /** @var bool
     * @Assert\Type(type="bool")
     * @ODM\Field(type="bool")
     *
     */
    private $isIgnoreRestrictions = false;

    /**
     * @var bool
     * @Assert\Type(type="bool")
     * @ODM\Field(type="bool")
     */
    private $isForceBooking = false;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @ODM\Field(type="string")
     */
    private $searchHash = '';

    /**
     * @var int
     * @Assert\Type(type="int")
     * @ODM\Field(type="int")
     */
    private $expectedResultsCount = 0;

    /**
     * @var int
     * @Assert\Type(type="int")
     * @ODM\Field(type="int")
     */
    private $additionalResultsLimit = 1;


    /**
     * @var bool
     * @Assert\Type(type="bool")
     * @ODM\Field(type="bool")
     */
    private $isUseCache = true;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @ODM\Field(type="string")
     */
    private $order;

    /** @var bool */
    private $isSpecialStrict = false;

    /** @var bool */
    private $isThisWarmUp = false;

    private $errorLevel = ErrorResultFilter::TARIFF_LIMIT;


    /**
     * SearchConditions constructor.
     */
    public function __construct()
    {
        $this->hotels = new ArrayCollection();
        $this->roomTypes = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
    }


    public function setId($id): SearchConditions
    {
        $this->id = $id;

        return $this;
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

    public function getHotels(): Collection
    {
        return $this->hotels;
    }

    public function addRoomTypes(RoomType $roomType): SearchConditions
    {
        $this->roomTypes->add($roomType);

        return $this;
    }

    /** TODO: Тут может жить категория, нужно фиксить
     * @param $roomTypes
     * @return SearchConditions
     */
    public function setRoomTypes($roomTypes): SearchConditions
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRoomTypes(): Collection
    {
        return $this->roomTypes;
    }

    /**
     * @return Collection|Tariff[]
     */
    public function getTariffs(): Collection
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
            } else {
                $restrictionTariffs[] = $tariff;
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

    public function isIgnoreRestrictions(): bool
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
     * @return int
     */
    public function getExpectedResultsCount(): int
    {
        return $this->expectedResultsCount;
    }

    /**
     * @param int $expectedResultsCount
     * @return SearchConditions
     */
    public function setExpectedResultsCount(int $expectedResultsCount): SearchConditions
    {
        $this->expectedResultsCount = $expectedResultsCount;

        return $this;
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

    public function getMaxBegin(): \DateTime
    {
        return (clone $this->getBegin())->modify("-{$this->getAdditionalBegin()} days");
    }

    public function getMaxEnd(): \DateTime
    {
        return (clone $this->getEnd())->modify("+{$this->getAdditionalEnd()} days");
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return SearchConditions
     */
    public function setOrder(string $order): SearchConditions
    {
        $this->order = $order;

        return $this;
    }


    /**
     * @return bool
     */
    public function isUseCache(): bool
    {
        return $this->isUseCache;
    }

    /**
     * @param bool $isUseCache
     * @return SearchConditions
     */
    public function setIsUseCache(bool $isUseCache): SearchConditions
    {
        $this->isUseCache = $isUseCache;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalResultsLimit(): int
    {
        return $this->additionalResultsLimit;
    }

    /**
     * @param int $additionalResultsLimit
     * @return SearchConditions
     */
    public function setAdditionalResultsLimit(int $additionalResultsLimit): SearchConditions
    {
        $this->additionalResultsLimit = $additionalResultsLimit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSpecialStrict(): bool
    {
        return $this->isSpecialStrict;
    }

    /**
     * @param bool $isSpecialStrict
     * @return SearchConditions
     */
    public function setIsSpecialStrict(bool $isSpecialStrict): SearchConditions
    {
        $this->isSpecialStrict = $isSpecialStrict;

        return $this;
    }

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return SearchConditions
     */
    public function setSpecial(Special $special): SearchConditions
    {
        $this->special = $special;

        return $this;
    }



    public function isThisWarmUp(): bool
    {
        return $this->isThisWarmUp;
    }

    /**
     * @param bool $isThisWarmUp
     * @return SearchConditions
     */
    public function setIsThisWarmUp(bool $isThisWarmUp): SearchConditions
    {
        $this->isThisWarmUp = $isThisWarmUp;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorLevel(): int
    {
        return $this->errorLevel;
    }

    /**
     * @param int $errorLevel
     * @return SearchConditions
     */
    public function setErrorLevel(int $errorLevel): SearchConditions
    {
        $this->errorLevel = $errorLevel;

        return $this;
    }




}