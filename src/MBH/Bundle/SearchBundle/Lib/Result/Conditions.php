<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


class Conditions implements \JsonSerializable
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

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Conditions
     */
    public function setId(string $id): Conditions
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
     * @return Conditions
     */
    public function setBegin(\DateTime $begin): Conditions
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
     * @return Conditions
     */
    public function setEnd(\DateTime $end): Conditions
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
     * @return Conditions
     */
    public function setAdults(int $adults): Conditions
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
     * @return Conditions
     */
    public function setChildren(int $children): Conditions
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
     * @return Conditions
     */
    public function setChildrenAges(array $childrenAges): Conditions
    {
        $this->childrenAges = $childrenAges;

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
            'childrenAges' => $this->getChildrenAges()
        ];
    }


}