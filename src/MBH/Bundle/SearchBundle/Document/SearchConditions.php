<?php


namespace MBH\Bundle\SearchBundle\Document;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @var array|int[]
     */
    private $childrenAges = [];

    /**
     * @var  ArrayCollection|RoomType[]
     */
    private $roomTypes = [];

    private $tariff;


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
    public function setBegin(\DateTime $begin): SearchConditions
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
    public function setEnd(\DateTime $end): SearchConditions
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

    public function setRoomTypes(array $roomTypes): SearchConditions
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    public function getRoomTypes(): ?array
    {
        return $this->roomTypes;
    }


}