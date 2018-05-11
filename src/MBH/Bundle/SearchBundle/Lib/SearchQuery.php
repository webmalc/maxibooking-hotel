<?php


namespace MBH\Bundle\SearchBundle\Lib;


use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Validator\Constraints\ChildrenAgesSameAsChildren;

/**
 * Class SearchQuery
 * @package MBH\Bundle\SearchBundle\Lib
 * @ChildrenAgesSameAsChildren()
 */
class SearchQuery
{
    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var string
     */
    private $tariffId;

    /**
     * @var string
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

    /** @var int  */
    private $childAge;

    /** @var int  */
    private $infantAge;

    /** @var SearchConditions */
    private $searchCondition;

    /**
     * @var bool
     */
    private $isRestrictionsWhereChecked = false;

    private $isIgnoreRestrictions = false;

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
    public function getSearchCondition(): SearchConditions
    {
        return $this->searchCondition;
    }

    /**
     * @param SearchConditions $searchCondition
     * @return SearchQuery
     */
    public function setSearchCondition(SearchConditions $searchCondition): SearchQuery
    {
        $this->searchCondition = $searchCondition;

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
     * @param int $childAge
     * @return SearchQuery
     */
    public function setChildAge(int $childAge): SearchQuery
    {
        $this->childAge = $childAge;

        return $this;
    }

    /**
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

    public function getSearchTotalPlaces(): int
    {
        $actualAdults = $this->getActualAdults();
        $actualChildren = $this->getActualChildren();

        return $actualAdults + $actualChildren;
    }

    public function getActualAdults(): int
    {
        $actualAdultAges =  array_filter(
            $this->childrenAges,
            function ($age) {
                return $age > $this->childAge;
            }
        );

        return $this->adults + \count($actualAdultAges);
    }

    public function getActualChildren(): int
    {
        $actualChildrenAges =  array_filter(
            $this->childrenAges,
            function ($age) {
                return $age >= $this->infantAge && $age <= $this->childAge;
            }
        );

        return \count($actualChildrenAges);
    }

    public function getInfants(): int
    {
        $infants =  array_filter(
            $this->childrenAges,
            function ($age) {
                return $age <= $this->infantAge;
            }
        );

        return \count($infants);
    }






public static function createInstance(SearchQueryHelper $queryHelper, SearchConditions $conditions): SearchQuery
    {
        $searchQuery = new static();


        $searchQuery
            ->setBegin($queryHelper->getBegin())
            ->setEnd($queryHelper->getEnd())
            ->setTariffId($queryHelper->getTariffId())
            ->setRoomTypeId($queryHelper->getRoomTypeId())
            ->setChildAge($queryHelper->getChildAge())
            ->setInfantAge($queryHelper->getInfantAge())
            ->setSearchCondition($conditions)
            ->setIgnoreRestrictions($conditions->isIgnoreRestrictoins())
            ->setChildren($conditions->getChildren())
            ->setAdults($conditions->getAdults())
            ->setChildrenAges($conditions->getChildrenAges())
        ;

        return $searchQuery;
    }
}