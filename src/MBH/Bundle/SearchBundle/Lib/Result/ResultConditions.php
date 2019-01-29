<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

class ResultConditions
{

    /** @var string */
    private $id;
    /** @var \DateTime */
    private $begin;
    /** @var \DateTime */
    private $end;
    /** @var int */
    private $adults;
    /** @var int */
    private $children;
    /** @var array */
    private $childrenAges;
    /** @var string */
    private $searchHash;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ResultConditions
     */
    public function setId(string $id): ResultConditions
    {
        $this->id = $id;

        return $this;
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
     * @return ResultConditions
     */
    public function setBegin(\DateTime $begin): ResultConditions
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
     * @return ResultConditions
     */
    public function setEnd(\DateTime $end): ResultConditions
    {
        $this->end = $end;

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
     * @return ResultConditions
     */
    public function setAdults(int $adults): ResultConditions
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
     * @return ResultConditions
     */
    public function setChildren(int $children): ResultConditions
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
     * @return ResultConditions
     */
    public function setChildrenAges(array $childrenAges): ResultConditions
    {
        $this->childrenAges = $childrenAges;
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
     * @return ResultConditions
     */
    public function setSearchHash(string $searchHash): ResultConditions
    {
        $this->searchHash = $searchHash;
        return $this;
    }


    public static function createInstance(SearchConditions $conditions): ResultConditions
    {
        $searchConditions = new self();

        $searchConditions
            ->setId($conditions->getId())
            ->setBegin($conditions->getBegin())
            ->setEnd($conditions->getEnd())
            ->setAdults($conditions->getAdults())
            ->setChildren($conditions->getChildren() ?? 0)
            ->setChildrenAges($conditions->getChildrenAges() ?? [])
            ->setSearchHash($conditions->getSearchHash())
        ;

        return $searchConditions;
    }


}