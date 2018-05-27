<?php

namespace MBH\Bundle\PackageBundle\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\PackageBundle\Document\CalculatedPackagePrices;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackagePriceForCombination;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 * Class SearchResult
 * @package MBH\Bundle\PackageBundle\Lib
 */
class SearchResult
{
    /**
     * @ODM\Field(type="date")
     * @var \DateTime
     */
    protected $begin;

    /**
     * @ODM\Field(type="date")
     * @var \DateTime 
     */
    protected $end;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    protected $adults;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    protected $children;

    /**
     * @ODM\ReferenceOne(targetDocument="RoomType")
     * @var RoomType
     */
    protected $roomType;

    /**
     * @ODM\ReferenceOne(targetDocument="Room")
     * @var Room
     */
    protected $virtualRoom;

    /**
     * @ODM\ReferenceOne(targetDocument="Tariff")
     * @var Tariff 
     */
    protected $tariff;

    /**
     * @ODM\Field(type="int")
     * @var int 
     */
    protected $roomsCount = 0;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    protected $packagesCount = 0;

    /**
     * @ODM\ReferenceMany(targetDocument="Room")
     * @var array
     */
    protected $rooms = [];

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    protected $useCategories = false;

    /**
     * @ODM\Field(type="bool")
     * @var bool
     */
    protected $forceBooking = false;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    protected $infants = 0;

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    protected $queryId;

    /**
     * @ODM\ReferenceOne(targetDocument="CalculatedPackagePrice")
     * @var CalculatedPackagePrices
     */
    protected $packagePrices;

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
     * @param CalculatedPackagePrices $calculatedPackagePrice
     * @return \MBH\Bundle\PackageBundle\Lib\SearchResult
     */
    public function setPackagePrices(CalculatedPackagePrices $calculatedPackagePrice)
    {
        $this->packagePrices = $calculatedPackagePrice;

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
     * @return RoomTypeInterface|Base
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
     * @return PackagePriceForCombination[]
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
    public function setPackagePricesForCombination(array $packagePrices, $adults, $children)
    {
        $this->packagePrices[$adults . '_' . $children] = $packagePrices;

        return $this;
    }

    /**
     * @param $packagePrices
     * @return SearchResult
     */
    public function setPackagePrices($packagePrices)
    {
        $this->packagePrices = $packagePrices;

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
     * @return \SplObjectStorage
     */
    public function getPriceTariffs()
    {
        $tariffs = new \SplObjectStorage();

        if (!count($this->packagePrices)) {
            return $tariffs;
        }

        /** @var PackagePrice $packagePrice */
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
    public function setVirtualRoom(?Room $virtualRoom): SearchResult
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
