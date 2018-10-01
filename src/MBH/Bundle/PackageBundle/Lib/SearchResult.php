<?php

namespace MBH\Bundle\PackageBundle\Lib;

use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Tariff;

class SearchResult
{

    /**
     * @var \DateTime 
     */
    protected $begin;

    /**
     * @var \DateTime 
     */
    protected $end;

    /**
     * @var int
     */
    protected $adults;

    /**
     * @var int
     */
    protected $children;

    /**
     * @var RoomType
     */
    protected $roomType;

    /**
     * @var Room
     */
    protected $virtualRoom;

    /**
     * @var Tariff 
     */
    protected $tariff;

    /**
     * mixed array of prices
     * 
     * @var []
     */
    protected $prices = [];

    /**
     * mixed array of pricesByDate
     *
     * @var []
     */
    protected $pricesByDate = [];

    /**
     * @var int 
     */
    protected $roomsCount = 0;

    /**
     * @var int
     */
    protected $packagesCount = 0;

    /**
     * @var array
     */
    protected $rooms = [];

    /**
     * @var PackagePrice[]
     */
    protected $packagePrices = [];

    /**
     * @var bool
     */
    protected $useCategories = false;

    protected $forceBooking = false;

    protected $infants = 0;

    /** @var string */
    protected $queryId;

    /**
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getRoomType()
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
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setTariff(Tariff $tariff)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->getNights() + 1;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->end->diff($this->begin)->format("%a");
    }

    /**
     * @return int
     */
    public function getRoomsCount()
    {
        return (int) $this->roomsCount;
    }

    /**
     * @param int $roomsCount
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setRoomsCount($roomsCount)
    {
        $this->roomsCount = (int) $roomsCount;

        return $this;
    }

    /**
     * @param mixed $price
     * @param mixed $adults
     * @param mixed $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function addPrice($price, $adults = null, $children = null)
    {
        if ($adults !== null && $children !== null) {

            if ($this->getAdults() !== 0 || $this->getChildren() !== 0) {
                if(($adults != $this->getAdults()) || ($children != $this->getChildren())) {
                    return $this;
                }
            }
        }

        if($price === null) {
            if(isset($this->prices[$adults . '_' . $children])) {
                unset($this->prices[$adults . '_' . $children]);
            }
            return $this;
        }

        if (!isset($this->prices[$adults . '_' . $children])) {
            $this->prices[$adults . '_' . $children] = 0;
        }
        $this->prices[$adults . '_' . $children] += (float) $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;

        return $this;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @param int $adults
     * @param int $children
     * @return int|null
     */
    public function getPrice($adults, $children)
    {
        if(!isset($this->getPrices()[$adults . '_' . $children])) {
            return null;
        }

        return (float) $this->getPrices()[$adults . '_' . $children];
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    public function getPricesByDate()
    {
        return $this->pricesByDate;
    }

    /**
     * @param $adults
     * @param $children
     * @return null|array
     */
    public function getPricesByDateForCombination($adults, $children)
    {
        if (isset($this->pricesByDate[$adults . '_' . $children])) {
            return $this->pricesByDate[$adults . '_' . $children];
        }

        return null;
    }

    /**
     * @param array $prices
     * @param int $adults
     * @param int $children
     * @return self
     * @deprecated
     */
    public function setPricesByDate(array $prices, $adults, $children)
    {
        $this->pricesByDate[$adults . '_' . $children] = $prices;

        return $this;
    }

    /**
     * @return array|Cursor
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param array $rooms
     * @return SearchResult
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUseCategories()
    {
        return $this->useCategories;
    }

    /**
     * @param boolean $useCategories
     * @return SearchResult
     */
    public function setUseCategories($useCategories)
    {
        $this->useCategories = $useCategories;

        return $this;
    }

    /**
     * @param bool $full
     * @return null|string
     */
    public function getRoomTypeTitle($full = false)
    {
        $roomType = $this->getRoomTypeInterfaceObject();

        if (!$roomType) {
            return null;
        }

        return $full ? $roomType->getFullTitle() : $roomType->getName();
    }

    /**
     * @return RoomTypeInterface
     */
    public function getRoomTypeInterfaceObject()
    {
        $roomType = $this->getRoomType();

        if (!$roomType) {
            return null;
        }
        if ($this->isUseCategories()) {
            $roomType = $this->getRoomType()->getCategory();
        }

        return $roomType;
    }

    /**
     * @return PackagePrice[]
     */
    public function getPackagePrices()
    {
        return $this->packagePrices;
    }

    /**
     * @param $adults
     * @param $children
     * @return null|array
     */
    public function getPackagePricesForCombination($adults, $children)
    {
        if (isset($this->packagePrices[$adults . '_' . $children])) {
            return $this->packagePrices[$adults . '_' . $children];
        }

        return null;
    }

    /**
     * @param array $packagePrices
     * @param int $adults
     * @param int $children
     * @return SearchResult
     */
    public function setPackagePrices(array $packagePrices, $adults, $children)
    {
        $this->packagePrices[$adults . '_' . $children] = $packagePrices;

        return $this;
    }

    /**
     * @return int
     */
    public function getPackagesCount()
    {
        return $this->packagesCount;
    }

    /**
     * @param int $packagesCount
     * @return SearchResult
     */
    public function setPackagesCount($packagesCount)
    {
        $this->packagesCount = $packagesCount;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getForceBooking()
    {
        return $this->forceBooking;
    }

    /**
     * @param boolean $forceBooking
     * @return SearchResult
     */
    public function setForceBooking($forceBooking)
    {
        $this->forceBooking = $forceBooking;
        return $this;
    }

    /**
     * @return int
     */
    public function getInfants()
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     * @return SearchResult
     */
    public function setInfants($infants)
    {
        $this->infants = $infants;
        return $this;
    }

    /**
     * @return SplObjectStorage
     */
    public function getPriceTariffs()
    {
        $tariffs = new \SplObjectStorage();

        if (!count($this->packagePrices)) {
            return $tariffs;
        }

        foreach (array_values($this->packagePrices)[0] as $packagePrice) {
            $tariffs->attach($packagePrice->getTariff());
        }

        return $tariffs;
    }

    /**
     * @return Room
     */
    public function getVirtualRoom()
    {
        return $this->virtualRoom;
    }

    /**
     * @param Room $virtualRoom
     * @return SearchResult
     */
    public function setVirtualRoom(Room $virtualRoom): SearchResult
    {
        $this->virtualRoom = $virtualRoom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQueryId(): ?string
    {
        return $this->queryId;
    }

    /**
     * @param mixed $queryId
     * @return SearchResult
     */
    public function setQueryId(string $queryId)
    {
        $this->queryId = $queryId;

        return $this;
    }

    /**
     * @return array
     */
    public function getJsonSerialized()
    {
        $packagePrices = [];
        $originalPrice = 0;
        /** @var PackagePrice $packagePrice */
        foreach ($this->getPackagePricesForCombination($this->getAdults(), $this->getChildren()) as $packagePrice) {
            $packagePrices[] = $packagePrice->getJsonSerialized();
            $originalPrice += $packagePrice->getPriceWithoutPromotionDiscount();
        }

        $data = [
            'begin' => $this->getBegin()->format(ApiHandler::DATE_FORMAT),
            'end' => $this->getEnd()->format(ApiHandler::DATE_FORMAT),
            'adults' => $this->getAdults(),
            'children' => $this->getChildren(),
            'roomType' => $this->getRoomType()->getId(),
            'tariff' => $this->getTariff()->getId(),
            'price' => $this->getPrice($this->getAdults(), $this->getChildren()),
            'priceWithoutPromotionDiscount' => round($originalPrice, 2),
            'prices' => $this->prices,
            'packagePrices' => $packagePrices,
            'roomsCount' => $this->getRoomsCount(),
            'nights' => (int)$this->getNights()
        ];

        return $data;
    }
}
