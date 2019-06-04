<?php


namespace MBH\Bundle\SearchBundle\Services\Calc;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\CalcHelperException;
use Symfony\Component\Validator\Constraints as Assert;

class CalcQuery
{
    /**
     * @var \DateTime
     * @Assert\Date()
     */
    private $searchBegin;

    /**
     * @var \DateTime
     * @Assert\NotNull()
     * @Assert\Date()
     */
    private $searchEnd;

    /** @var \DateTime */
    private $conditionMaxBegin;

    /** @var \DateTime */
    private $conditionMaxEnd;

    /** @var Collection|Tariff[] */
    private $conditionTariffs;

    /** @var Collection|RoomType[] */
    private $conditionRoomTypes;

    /** @var string */
    private $conditionHash;

    /**
     * @var RoomType
     * @Assert\NotNull()
     */
    private $roomType;

    /**
     * @var Tariff
     * @Assert\NotNull()
     */
    private $tariff;

    /**
     * @var int
     * @Assert\Type(type="int")
     */
    private $actualAdults = 0;

    /**
     * @var int
     * @Assert\Type(type="int")
     */
    private $actualChildren = 0;

    /** @var Promotion */
    private $promotion;

    /** @var Special */
    private $special;

    /**
     * @var bool
     * @Assert\Type(type="bool")
     * @Assert\NotNull()
     */
    private $isStrictDuration = true;

    /** @var SearchConditions */
    private $conditions;


    public function __construct()
    {
        $this->conditionTariffs = new ArrayCollection();
        $this->conditionRoomTypes = new ArrayCollection();
    }


    /**
     * @return \DateTime
     */
    public function getSearchBegin(): \DateTime
    {
        return $this->searchBegin;
    }

    /**
     * @param \DateTime $searchBegin
     * @return CalcQuery
     */
    public function setSearchBegin(\DateTime $searchBegin): CalcQuery
    {
        $this->searchBegin = $searchBegin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSearchEnd(): \DateTime
    {
        return $this->searchEnd;
    }

    /**
     * @param \DateTime $searchEnd
     * @return CalcQuery
     */
    public function setSearchEnd(\DateTime $searchEnd): CalcQuery
    {
        $this->searchEnd = $searchEnd;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConditionMaxBegin(): ?\DateTime
    {
        if (!$this->conditionMaxBegin) {
            return $this->searchBegin;
        }

        return $this->conditionMaxBegin;
    }

    /**
     * @param \DateTime $conditionMaxBegin
     * @return CalcQuery
     */
    public function setConditionMaxBegin(\DateTime $conditionMaxBegin): CalcQuery
    {
        $this->conditionMaxBegin = $conditionMaxBegin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConditionMaxEnd(): ?\DateTime
    {
        if (!$this->conditionMaxEnd) {
            return $this->getSearchEnd();
        }

        return $this->conditionMaxEnd;
    }

    /**
     * @param \DateTime $conditionMaxEnd
     * @return CalcQuery
     */
    public function setConditionMaxEnd(\DateTime $conditionMaxEnd): CalcQuery
    {
        $this->conditionMaxEnd = $conditionMaxEnd;

        return $this;
    }

    /**
     * @return
     */
    public function getConditionPricedTariffs(): array
    {
        $result = [];
        foreach ($this->conditionTariffs as $tariff) {
            if ($tariff->getParent() && $tariff->getChildOptions()->isInheritPrices()) {
                $result[] =$tariff->getParent()->getId();
            } else {
                $result[] = $tariff->getId();
            }
        }

        return array_unique($result);
    }

    /**
     * @param Collection|Tariff[] $conditionTariffs
     * @return CalcQuery
     */
    public function setConditionTariffs(Collection $conditionTariffs): CalcQuery
    {
        $this->conditionTariffs = $conditionTariffs;

        return $this;
    }

    /**
     * @return Collection|RoomType[]|RoomTypeCategory[]
     */
    public function getConditionRoomTypes(): ?Collection
    {
        return $this->conditionRoomTypes;
    }

    /**
     * @param Collection|RoomType[]|RoomTypeCategory[] $conditionRoomTypes
     * @return CalcQuery
     */
    public function setConditionRoomTypes(Collection $conditionRoomTypes)
    {
        $this->conditionRoomTypes = $conditionRoomTypes;

        return $this;
    }

    /**
     * @return string
     */
    public function getConditionHash(): ?string
    {
        return $this->conditionHash;
    }

    /**
     * @param string $conditionHash
     * @return CalcQuery
     */
    public function setConditionHash(string $conditionHash): CalcQuery
    {
        $this->conditionHash = $conditionHash;

        return $this;
    }



    /**
     * @return RoomType
     */
    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return CalcQuery
     */
    public function setRoomType(RoomType $roomType): CalcQuery
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return Tariff
     */
    public function getTariff(): Tariff
    {
        return $this->tariff;
    }

    /**
     * @param Tariff $tariff
     * @return CalcQuery
     */
    public function setTariff(Tariff $tariff): CalcQuery
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return int
     */
    public function getActualAdults(): int
    {
        return $this->actualAdults;
    }

    /**
     * @param int $actualAdults
     * @return CalcQuery
     */
    public function setActualAdults(int $actualAdults): CalcQuery
    {
        $this->actualAdults = $actualAdults;

        return $this;
    }

    /**
     * @return int
     */
    public function getActualChildren(): int
    {
        return $this->actualChildren;
    }

    /**
     * @param int $actualChildren
     * @return CalcQuery
     */
    public function setActualChildren(int $actualChildren): CalcQuery
    {
        $this->actualChildren = $actualChildren;

        return $this;
    }

    /**
     * @return Promotion
     */
    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    /**
     * @param Promotion $promotion
     * @return CalcQuery
     */
    public function setPromotion(Promotion $promotion): CalcQuery
    {
        $this->promotion = $promotion;

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
     * @return CalcQuery
     */
    public function setSpecial(Special $special): CalcQuery
    {
        $this->special = $special;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStrictDuration(): bool
    {
        return $this->isStrictDuration;
    }

    /**
     * @param bool $isStrictDuration
     * @return CalcQuery
     */
    public function setIsStrictDuration(bool $isStrictDuration): CalcQuery
    {
        $this->isStrictDuration = $isStrictDuration;

        return $this;
    }


    public function getDuration(): int
    {
        return (int)$this->searchEnd->diff($this->searchBegin)->format('%a');
    }

    public function getPriceCacheEnd(): \DateTime
    {
        return (clone $this->searchEnd)->modify('-1 day');
    }

//    public function getPriceTariffId(): string
//    {
//        if ($this->tariff->getParent() && $this->tariff->getChildOptions()->isInheritPrices()) {
//            return $this->tariff->getParent()->getId();
//        }
//
//        return $this->tariff->getId();
//    }

    public function getMergingTariffId(): ?string
    {
        if ($mergingTariff = $this->tariff->getMergingTariff()) {
            return $mergingTariff->getId();
        }

        return null;
    }

    public function getPriceRoomTypeId(): string
    {
        if ($this->isUseCategory) {
            if (null === $this->roomType->getCategory()) {
                throw new CalcHelperException('Categories in use, but RoomType hasn\'t category');
            }

            return $this->roomType->getCategory()->getId();
        }

        return $this->roomType->getId();
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
     * @return CalcQuery
     */
    public function setSearchConditions(SearchConditions $conditions): CalcQuery
    {
        $this->conditions = $conditions;

        return $this;
    }




}