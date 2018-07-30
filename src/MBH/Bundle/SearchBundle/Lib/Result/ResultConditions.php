<?php


namespace MBH\Bundle\SearchBundle\Lib\Result;


use MBH\Bundle\SearchBundle\Document\SearchConditions;

class ResultConditions implements \JsonSerializable
{

    /** @var SearchConditions */
    private $conditions;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->conditions->getId();
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->conditions->getBegin();
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->conditions->getEnd();
    }

    /**
     * @return int
     */
    public function getAdults(): int
    {
        return $this->conditions->getAdults();
    }

    /**
     * @return int
     */
    public function getChildren(): int
    {
        return $this->conditions->getChildren() ?? 0;
    }

    /**
     * @return array
     */
    public function getChildrenAges(): array
    {
        return $this->conditions->getChildrenAges() ?? [];
    }

    /**
     * @return SearchConditions
     */
    public function getConditions(): SearchConditions
    {
        return $this->conditions;
    }

    /**
     * @param SearchConditions $conditions
     * @return ResultConditions
     */
    public function setConditions(SearchConditions $conditions): ResultConditions
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function isForceBooking(): bool
    {
        return $this->conditions->isForceBooking();
    }

    public function getSearchHash(): string
    {
        return $this->conditions->getSearchHash();
    }

    public function getOrder(): ?string
    {
        return $this->conditions->getOrder();
    }




    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'begin' => $this->getBegin()->format('d.m.Y'),
            'end' => $this->getEnd()->format('d.m.Y'),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'childrenAges' => $this->getChildrenAges(),
            'hash' => $this->getSearchHash(),
            'order' => $this->getOrder(),
            'forceBooking' => $this->isForceBooking()
        ];
    }


}