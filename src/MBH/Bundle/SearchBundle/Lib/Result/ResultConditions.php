<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

class ResultConditions implements \JsonSerializable
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
    private $hash;
    /** @var bool */
    private $isForceBooking;

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
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return ResultConditions
     */
    public function setHash(string $hash): ResultConditions
    {
        $this->hash = $hash;
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
     * @return ResultConditions
     */
    public function setIsForceBooking(bool $isForceBooking): ResultConditions
    {
        $this->isForceBooking = $isForceBooking;
        return $this;
    }




    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'begin' => $this->getBegin()->format('d-m-Y'),
            'end' => $this->getEnd()->format('d-m-Y'),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'childrenAges' => $this->getChildrenAges(),
            'hash' => $this->getHash()
        ];
    }

    public static function createInstance(SearchConditions $conditions): ResultConditions
    {
        $searchConditions = new self();

        $searchConditions
            ->setId($conditions->getId())
            ->setBegin($conditions->getBegin())
            ->setEnd($conditions->getEnd())
            ->setAdults($conditions->getAdults())
            ->setChildrenAges($conditions->getChildrenAges())
            ->setHash($conditions->getSearchHash())
            ->setIsForceBooking($conditions->isForceBooking())
        ;

        return $searchConditions;
    }


}