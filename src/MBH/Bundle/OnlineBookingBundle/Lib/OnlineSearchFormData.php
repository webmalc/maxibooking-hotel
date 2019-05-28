<?php
namespace MBH\Bundle\OnlineBookingBundle\Lib;


use DateTime;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\Special;

/**
 * Class OnlineSearchFormInstance
 * @package MBH\Bundle\OnlineBookingBundle\Lib
 */
class OnlineSearchFormData
{
    /** @var  RoomTypeManager */
    private $roomTypeManager;
    /** @var  Hotel */
    protected $hotel;
    /** @var  RoomType */
    protected $roomType;
    /** @var DateTime*/
    protected $begin;
    /** @var  DateTime */
    protected $end;
    /** @var  int */
    protected $adults;
    /** @var  int */
    protected $children;
    /** @var  array */
    protected $childrenAge;
    /** @var  Special */
    protected $special;
    /** @var bool  */
    protected $cache = true;
    /** @var bool */
    protected $addDates = false;
    /** @var bool */
    protected $forceSearchDisabledSpecial = false;
    /** @var bool */
    protected $forceCapacityRestriction = false;

    /**
     * OnlineSearchFormData constructor.
     * @param RoomTypeManager $roomTypeManager
     */
    public function __construct(RoomTypeManager $roomTypeManager)
    {
        $this->roomTypeManager = $roomTypeManager;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return OnlineSearchFormData
     */
    public function setHotel(Hotel $hotel): OnlineSearchFormData
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    /**
     * @param RoomType $roomType
     * @return $this
     */
    public function setRoomType(RoomType $roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBegin(): ?DateTime
    {
        return $this->begin;
    }

    /**
     * @param DateTime $begin
     * @return $this
     */
    public function setBegin(?DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     * @return $this
     */
    public function setEnd(?DateTime $end)
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
     * @return $this
     */
    public function setAdults(int $adults)
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
     * @return $this
     */
    public function setChildren(int $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildrenAge(): ?array
    {
        return $this->childrenAge;
    }

    /**
     * @param array $childrenAge
     * @return $this
     */
    public function setChildrenAge(array $childrenAge)
    {
        $this->childrenAge = $childrenAge;

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
     * @return $this
     */
    public function setSpecial(Special $special)
    {
        $this->special = $special;

        return $this;
    }

    public function getActualRoomTypeIds(): array
    {
        $result = [];
        if (null !== $this->getHotel()) {
            if ($this->roomTypeManager->useCategories) {
                $roomTypes = $this->hotel->getRoomTypesCategories();
            } else {
                $roomTypes = $this->hotel->getRoomTypes();
            }
            foreach ($roomTypes as $roomType) {
                $result[] = $roomType->getId();
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }

    /**
     * @param bool $cache
     */
    public function setCache(bool $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function isAddDates(): bool
    {
        return $this->addDates;
    }

    /**
     * @param bool $addDates
     * @return $this
     */
    public function setAddDates(bool $addDates)
    {
        $this->addDates = $addDates;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForceSearchDisabledSpecial(): bool
    {
        return $this->forceSearchDisabledSpecial;
    }

    /**
     * @param bool $forceSearchDisabledSpecial
     */
    public function setForceSearchDisabledSpecial(bool $forceSearchDisabledSpecial)
    {
        $this->forceSearchDisabledSpecial = $forceSearchDisabledSpecial;
    }

    /**
     * @return bool
     */
    public function isForceCapacityRestriction()
    {
        return $this->forceCapacityRestriction;
    }

    /**
     * @param bool $forceCapacityRestriction
     */
    public function setForceCapacityRestriction(bool $forceCapacityRestriction)
    {
        $this->forceCapacityRestriction = $forceCapacityRestriction;
    }





}