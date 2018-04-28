<?php


namespace MBH\Bundle\SearchBundle\Lib;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

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
    private $childrenAges;

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
    public function getTariffId(): string
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


public static function createInstance(SearchQueryHelper $queryHelper, SearchConditions $conditions): SearchQuery
    {
        $searchQuery = new static();

        $searchQuery
            ->setSearchCondition($conditions)
            ->setBegin($queryHelper->getBegin())
            ->setEnd($queryHelper->getEnd())
            ->setAdults($conditions->getAdults())
            ->setChildren($conditions->getChildren())
            ->setTariffId($queryHelper->getTariffId())
            ->setRoomTypeId($queryHelper->getRoomTypeId())
            ->setIgnoreRestrictions($conditions->isIgnoreRestrictoins())
        ;

        return $searchQuery;
    }
}