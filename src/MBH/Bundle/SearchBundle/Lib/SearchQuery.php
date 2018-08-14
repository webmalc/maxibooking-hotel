<?php


namespace MBH\Bundle\SearchBundle\Lib;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchQuery
 * @package MBH\Bundle\SearchBundle\Lib
 * @ChildrenAgesSameAsChildren()
 */
class SearchQuery
{
    /**
     * @var \DateTime
     * @Assert\NotNull()
     */
    private $begin;

    /**
     * @var \DateTime
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @var string
     * @Assert\NotNull()
     */
    private $tariffId;

    /** @var string */
    private $restrictionTariffId;

    /**
     * @var string
     * @Assert\NotNull()
     */
    private $roomTypeId;

    /**
     * @var int
     */
    private $adults;
    /**
     * @var int
     */
    private $children;
    /**
     * @var array
     */
    private $childrenAges = [];

    /** @var int */
    private $childAge;

    /** @var int */
    private $infantAge;

    /**
     * @var SearchConditions
     */
    private $searchConditions;

    /** @var bool */
    private $isRestrictionsWhereChecked = false;

    /** @var bool  */
    private $isIgnoreRestrictions = false;

    /** @var bool */
    private $isForceBooking;

    /** @var string */
    private $searchHash;

    /**
     * @return mixed
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param mixed $begin
     * @return SearchQuery
     */
    public function setBegin($begin): SearchQuery
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $end
     * @return SearchQuery
     */
    public function setEnd($end): SearchQuery
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getTariffId(): ?string
    {
        return $this->tariffId;
    }

    /**
     * @param string $tariffId
     * @return SearchQuery
     */
    public function setTariffId(string $tariffId): SearchQuery
    {
        $this->tariffId = $tariffId;

        return $this;
    }

    public function getRestrictionTariffId(): ?string
    {
        return $this->restrictionTariffId;
    }

    public function setRestrictionTariffId(string $restrictionTariffId): SearchQuery
    {
        $this->restrictionTariffId = $restrictionTariffId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomTypeId(): string
    {
        return $this->roomTypeId;
    }

    /**
     * @param string $roomTypeId
     * @return SearchQuery
     */
    public function setRoomTypeId(string $roomTypeId): SearchQuery
    {
        $this->roomTypeId = $roomTypeId;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @param mixed $adults
     * @return SearchQuery
     */
    public function setAdults($adults): SearchQuery
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     * @return SearchQuery
     */
    public function setChildren($children): SearchQuery
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildrenAges()
    {
        return $this->childrenAges;
    }

    /**
     * @param mixed $childrenAges
     * @return SearchQuery
     */
    public function setChildrenAges($childrenAges): SearchQuery
    {
        $this->childrenAges = $childrenAges;

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
     * @return SearchQuery
     */
    public function setSearchConditions(SearchConditions $searchConditions): SearchQuery
    {
        $this->searchConditions = $searchConditions;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRestrictionsWhereChecked(): bool
    {
        return $this->isRestrictionsWhereChecked;
    }

    public function setRestrictionsWhereChecked(): SearchQuery
    {
        $this->isRestrictionsWhereChecked = true;

        return $this;
    }

    public function isIgnoreRestrictions(): bool
    {
        return $this->isIgnoreRestrictions;
    }

    public function setIgnoreRestrictions(bool $isIgnore): SearchQuery
    {
        $this->isIgnoreRestrictions = $isIgnore;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildAge(): int
    {
        return $this->childAge;
    }

    /**
     * @deprecated
     * TODO: Возраст будет браться из калькуляции взависимости от  тарифа priceCache (из за комбинирования тарифов)
     * @param int $childAge
     * @return SearchQuery
     */
    public function setChildAge(int $childAge): SearchQuery
    {
        $this->childAge = $childAge;

        return $this;
    }

    /**
     * @deprecated
     * TODO: Возраст будет браться из калькуляции взависимости от  тарифа priceCache (из за комбинирования тарифов)
     * @return int
     */
    public function getInfantAge(): int
    {
        return $this->infantAge;
    }

    /**
     * @param int $infantAge
     * @return SearchQuery
     */
    public function setInfantAge(int $infantAge): SearchQuery
    {
        $this->infantAge = $infantAge;

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
     * @return SearchQuery
     */
    public function setIsForceBooking(bool $isForceBooking): SearchQuery
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
     * @return SearchQuery
     */
    public function setSearchHash(string $searchHash): SearchQuery
    {
        $this->searchHash = $searchHash;

        return $this;
    }

    public function unsetConditions()
    {
        $this->searchConditions = null;
    }


    public function getSearchTotalPlaces(): int
    {
        $actualAdults = $this->getActualAdults();
        $actualChildren = $this->getActualChildren();

        return $actualAdults + $actualChildren;
    }

    public function getActualAdults(): int
    {
        $actualAdultAges = array_filter(
            $this->childrenAges,
            function ($age) {
                return $age > $this->childAge;
            }
        );

        return $this->adults + \count($actualAdultAges);
    }

    public function getActualChildren(): int
    {
        $actualChildrenAges = array_filter(
            $this->childrenAges,
            function ($age) {
                return $age >= $this->infantAge && $age <= $this->childAge;
            }
        );

        return \count($actualChildrenAges);
    }

    public function getInfants(): int
    {
        $infants = array_filter(
            $this->childrenAges,
            function ($age) {
                return $age <= $this->infantAge;
            }
        );

        return \count($infants);
    }

     public function getDuration(): int
    {
        return (int)$this->end->diff($this->begin)->format('%a');
    }


    public static function createInstance(SearchQueryHelper $queryHelper, SearchConditions $conditions): SearchQuery
    {
        $searchQuery = new static();
        $searchQuery
            ->setBegin($queryHelper->getBegin())
            ->setEnd($queryHelper->getEnd())
            ->setTariffId($queryHelper->getTariffId())
            ->setRestrictionTariffId($queryHelper->getRestrictionTariffId())
            ->setRoomTypeId($queryHelper->getRoomTypeId())
            ->setChildAge($queryHelper->getChildAge())
            ->setInfantAge($queryHelper->getInfantAge())
            ->setSearchConditions($conditions)
            ->setIgnoreRestrictions($conditions->isIgnoreRestrictions())
            ->setChildren($conditions->getChildren())
            ->setAdults($conditions->getAdults())
            ->setChildrenAges($conditions->getChildrenAges())
            ->setIsForceBooking($conditions->isForceBooking())
            ->setSearchHash($conditions->getSearchHash())
        ;

        return $searchQuery;
    }

}