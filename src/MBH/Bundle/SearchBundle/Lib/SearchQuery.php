<?php


namespace MBH\Bundle\SearchBundle\Lib;


use DateTime;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Services\Calc\CalcQueryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchQuery
 * @package MBH\Bundle\SearchBundle\Lib
 */
class SearchQuery implements CalcQueryInterface
{
    /**
     * @var DateTime
     * @Assert\NotNull()
     * @Assert\Date()
     */
    private $begin;

    /**
     * @var DateTime
     * @Assert\NotNull()
     * @Assert\Date()
     */
    private $end;

    /**
     * @var string
     * @Assert\NotNull()
     * @Assert\Type("string")
     */
    private $tariffId;

    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotNull()
     */
    private $roomTypeId;

    /** @var string */
    private $specialId;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Type("integer")
     */
    private $adults;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Type("integer")
     */
    private $children;
    /**
     * @var array
     * @Assert\Type("array")
     */
    private $childrenAges = [];

    /**
     * @var SearchConditions
     */
    private $searchConditions;

    /** @var bool
     * @Assert\Type("bool")
     */
    private $isRestrictionsWhereChecked = false;

    /**
     * @var bool
     * @Assert\Type("bool")
     */
    private $isIgnoreRestrictions = false;

    /**
     * @var bool
     * @Assert\Type("bool")
     */
    private $isForceBooking;

    /**
     * @var string
     * @Assert\Type("string")
     */
    private $searchHash;

    /** @var int */
    private $errorLevel = 0;

    /** @var string */
    private $searchConditionsId;

    /** @var bool */
    private $isWarmUp;

    /**
     * @return mixed
     */
    public function getBegin(): DateTime
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
    public function getEnd(): DateTime
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
        throw new \Exception('Change to service');
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

    public function getSpecialId(): ?string
    {
        return $this->specialId;
    }

    public function setSpecialId(string $specialId): SearchQuery
    {
        $this->specialId = $specialId;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getAdults(): int
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
     * @return int|null
     */
    public function getChildren(): ?int
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
    public function getChildrenAges(): array
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
    public function getSearchConditions(): SearchConditions
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
    public function isRestrictionsAlreadyChecked(): bool
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

    public function unsetConditions(): void
    {
        if ($this->searchConditions) {
            $this->searchConditionsId = $this->searchConditions->getId();
        }
        $this->searchConditions = null;
    }


     public function getDuration(): int
    {
        return (int)$this->end->diff($this->begin)->format('%a');
    }


    public function setErrorLevel(int $level): SearchQuery
    {
        $this->errorLevel = $level;

        return $this;
    }

    public function getErrorLevel(): int
    {
        return $this->errorLevel;
    }

    /**
     * @return string
     */
    public function getSearchConditionsId(): string
    {
        return $this->searchConditionsId;
    }

    /**
     * @return bool
     */
    public function isWarmUp(): bool
    {
        return $this->isWarmUp;
    }

    /**
     * @param bool $isWarmUp
     * @return SearchQuery
     */
    public function setIsWarmUp(bool $isWarmUp): SearchQuery
    {
        $this->isWarmUp = $isWarmUp;

        return $this;
    }

    public function isExtendedDataQuery(): bool
    {
        return null !== $this->searchConditions;
    }


    public static function createInstance(SearchConditions $conditions, DateTime $begin, DateTime $end, array $tariffRoomType): SearchQuery
    {
        $searchQuery = new static();
        $searchQuery
            ->setBegin($begin)
            ->setEnd($end)
            ->setTariffId($tariffRoomType['tariffId'])
            ->setRoomTypeId($tariffRoomType['roomTypeId'])
            ->setSearchConditions($conditions)
            ->setIgnoreRestrictions($conditions->isIgnoreRestrictions())
            ->setChildren($conditions->getChildren())
            ->setAdults($conditions->getAdults())
            ->setChildrenAges($conditions->getChildrenAges())
            ->setIsForceBooking($conditions->isForceBooking())
            ->setSearchHash($conditions->getSearchHash())
            ->setErrorLevel($conditions->getErrorLevel())
            ->setIsWarmUp($conditions->isThisWarmUp());

        if ($special = $conditions->getSpecial()) {
            $searchQuery->setSpecialId($special->getId());
        }

        return $searchQuery;
    }


}