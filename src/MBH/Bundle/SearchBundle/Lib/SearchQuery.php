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


public static function createInstance(SearchQueryHelper $queryHelper, SearchConditions $conditions): SearchQuery
    {
        $searchQuery = new static();

        $searchQuery
            ->setSearchCondition($conditions)
            ->setBegin($queryHelper->getBegin())
            ->setEnd($queryHelper->getEnd())
            ->setAdults($conditions->getAdults())
            ->setChildren($conditions->getChildren());

        return $searchQuery;
    }
}