<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Validator\Constraints\Range;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchConditions
 * @package MBH\Bundle\SearchBundle\Document
 * @Range()
 */
class SearchConditions
{
    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $begin;

    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @var int
     * @Assert\NotNull(message="form.searchType.adults_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 12,
     *     minMessage = "form.searchType.adults_amount_less_zero"
     * )
     */
    private $adults;

    /**
     * @var int
     * @Assert\NotNull(message="orm.searchType.children_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
     */
    private $children;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $additionalBefore = 0;

    /**
     * @var int
     * @Assert\Range(
     *     min=0,
     *     max=14
     * )
     */
    private $additionalAfter = 0;

    /**
     * @var array|int[]
     * @Assert\Collection()
     *
     */
    private $childrenAges = [];

    /**
     * @var  ArrayCollection|RoomType[]
     */
    private $roomTypes = [];

    /**
     * @var ArrayCollection|Tariff[]
     */
    private $tariffs;

    /**
     * SearchConditions constructor.
     */
    public function __construct()
    {
        $this->roomTypes = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
    }


    /**
     * @return \DateTime
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return SearchConditions
     */
    public function setBegin(?\DateTime $begin): SearchConditions
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return SearchConditions
     */
    public function setEnd(?\DateTime $end): SearchConditions
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return SearchConditions
     */
    public function setAdults(int $adults): SearchConditions
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren(): ?int
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return SearchConditions
     */
    public function setChildren(int $children): SearchConditions
    {
        $this->children = $children;

        return $this;
    }

    public function addRoomTypes(RoomType $roomType): SearchConditions
    {
        $this->roomTypes->add($roomType);

        return $this;
    }

    public function setRoomTypes($roomTypes): SearchConditions
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    public function getRoomTypes(): ArrayCollection
    {
        return $this->roomTypes;
    }

    /**
     * @return ArrayCollection|Tariff[]
     */
    public function getTariffs(): ArrayCollection
    {
        return $this->tariffs;
    }

    /**
     * @param ArrayCollection|Tariff[] $tariffs
     * @return SearchConditions
     */
    public function setTariffs(ArrayCollection $tariffs): SearchConditions
    {
        $this->tariffs = $tariffs;

        return $this;
    }

    public function addTariff(Tariff $tariff): void
    {
        $this->tariffs->add($tariff);
    }

    /**
     * @return int
     */
    public function getAdditionalBefore(): int
    {
        return $this->additionalBefore;
    }

    /**
     * @param int $additionalBefore
     * @return SearchConditions
     */
    public function setAdditionalBefore(?int $additionalBefore): SearchConditions
    {
        $this->additionalBefore = $additionalBefore;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdditionalAfter(): int
    {
        return $this->additionalAfter;
    }

    /**
     * @param int $additionalAfter
     * @return SearchConditions
     */
    public function setAdditionalAfter(?int $additionalAfter): SearchConditions
    {
        $this->additionalAfter = $additionalAfter;

        return $this;
    }

    /**
     * @return array|int[]
     */
    public function getChildrenAges(): ?array
    {
        return $this->childrenAges;
    }

    /**
     * @param array|int[] $childrenAges
     * @return SearchConditions
     */
    public function setChildrenAges(?array $childrenAges)
    {
        $this->childrenAges = $childrenAges;

        return $this;
    }








}