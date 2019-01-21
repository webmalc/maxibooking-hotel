<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\BaseBundle\Lib\Disableable as Disableable;

/**
 * @ODM\Document(collection="PriceCache", repositoryClass="MBH\Bundle\PriceBundle\Document\PriceCacheRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"roomType", "date", "tariff", "cancelDate"}, message="PriceCache already exist.")
 * @MongoDBUnique(fields={"roomTypeCategory", "date", "tariff", "cancelDate"}, message="PriceCache already exist.")
 * @ODM\HasLifecycleCallbacks
 * @Disableable\Disableable
 * @ODM\Index(keys={"hotel"="asc","roomType"="asc","tariff"="asc","date"="asc"})
 * @ODM\Index(name="search_enabled_date_roomType", keys={"isEnabled"="asc","date"="asc","roomType"="asc"})
 */
class PriceCache extends Base
{
    /**
     * @var \MBH\Bundle\HotelBundle\Document\Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * @var \MBH\Bundle\HotelBundle\Document\RoomTypeCategory
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomTypeCategory")
     * @ODM\Index()
     */
    protected $roomTypeCategory;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $tariff;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\Date()
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $date;

    /**
     * @var int
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(min=0)
     * @ODM\Index()
     */
    protected $price;

    /**
     * @var int
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     */
    protected $childPrice;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $isPersonPrice = false;

    /**
     * @var float
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     */
    protected $additionalPrice = null;

    /**
     * @var array
     * @ODM\Field(type="collection")
     * @Assert\Type(type="array")
     */
    protected $additionalPrices = [];

    /**
     * @var int
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     */
    protected $additionalChildrenPrice = null;

    /**
     * @var array
     * @ODM\Field(type="collection")
     * @Assert\Type(type="array")
     */
    protected $additionalChildrenPrices = [];

    /**
     * @var int
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     */
    protected $singlePrice = null;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @ODM\Index()
     * @Assert\Date()
     */
    protected $cancelDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $createdAt;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return PriceCache
     */
    public function setCreatedAt(\DateTime $createdAt): PriceCache
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCancelDate(): ?\DateTime
    {
        return $this->cancelDate;
    }

    /**
     * @param \DateTime $cancelDate
     * @param bool $isDisabled
     * @return PriceCache
     */
    public function setCancelDate(\DateTime $cancelDate, $isDisabled = false): PriceCache
    {
        $this->cancelDate = $cancelDate;
        if ($isDisabled) {
            $this->setIsEnabled(false);
        }

        return $this;
    }

    /**
     * Set hotel
     *
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return self
     */
    public function setHotel(\MBH\Bundle\HotelBundle\Document\Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     * @return self
     */
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType = null)
    {
        $this->roomType = $roomType;
        return $this;
    }

    /**
     * Get roomType
     *
     * @return \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @return self
     */
    public function setTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * Get tariff
     *
     * @return \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return self
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Set isPersonPrice
     *
     * @param boolean $isPersonPrice
     * @return self
     */
    public function setIsPersonPrice(bool $isPersonPrice): self
    {
        $this->isPersonPrice = $isPersonPrice;

        return $this;
    }

    /**
     * Get isPersonPrice
     *
     * @return boolean $isPersonPrice
     */
    public function getIsPersonPrice(): bool
    {
        return $this->isPersonPrice;
    }

    /**
     * Set additionalPrice
     *
     * @param int $additionalPrice
     * @return self
     */
    public function setAdditionalPrice(?float $additionalPrice): self
    {
        $this->additionalPrice = $additionalPrice;

        return $this;
    }

    /**
     * Get additionalPrice
     *
     * @return float|null $additionalPrice
     */
    public function getAdditionalPrice(): ?float
    {
        return $this->additionalPrice;
    }

    /**
     * Set additionalChildrenPrice
     *
     * @param float|null $additionalChildrenPrice
     * @return self
     */
    public function setAdditionalChildrenPrice(?float $additionalChildrenPrice): self
    {
        $this->additionalChildrenPrice = $additionalChildrenPrice;

        return $this;
    }

    /**
     * Get additionalChildrenPrice
     *
     * @return float|null $additionalChildrenPrice
     */
    public function getAdditionalChildrenPrice(): ?float
    {
        return $this->additionalChildrenPrice;
    }

    /**
     * Set singlePrice
     *
     * @param float|null $singlePrice
     * @return self
     */
    public function setSinglePrice(?float $singlePrice)
    {
        $this->singlePrice = $singlePrice;

        return $this;
    }

    /**
     * Get singlePrice
     *
     * @return float|null $singlePrice
     */
    public function getSinglePrice(): ?float
    {
        return $this->singlePrice;
    }

    /**
     * @param int $places
     * @param int $additionalPlaces
     * @return float|int
     */
    public function getMaxIncome($places = null, $additionalPlaces = null)
    {
        if (!$places) {
            $places = $this->getRoomType()->getPlaces();
        }
        if (!$additionalPlaces && $this->getRoomType()) {
            $additionalPlaces = $this->getRoomType()->getAdditionalPlaces();
        }

        if ($this->getIsPersonPrice()) {
            $commonPlace = $places * $this->getPrice();
        } else {
            $commonPlace = $this->getPrice();
        }
        $additionalPlace = $additionalPlaces * $this->getAdditionalPrice();
        return $commonPlace + $additionalPlace;
    }

    /**
     * @return int
     */
    public function getChildPrice()
    {
        return $this->childPrice;
    }

    /**
     * @param int $childPrice
     * @return PriceCache
     */
    public function setChildPrice($childPrice)
    {
        $this->childPrice = $childPrice;

        return $this;
    }

    /**
     * @return float[]
     */
    public function getAdditionalPrices(): array
    {
        return $this->additionalPrices;
    }

    /**
     * @param array $additionalPrices
     * @return PriceCache
     */
    public function setAdditionalPrices(array $additionalPrices)
    {
        $this->additionalPrices = $additionalPrices;

        return $this;
    }

    /**
     * @return float[]
     */
    public function getAdditionalChildrenPrices(): array
    {
        return $this->additionalChildrenPrices;
    }

    /**
     * @param array $additionalChildrenPrices
     * @return PriceCache
     */
    public function setAdditionalChildrenPrices(array $additionalChildrenPrices)
    {
        $this->additionalChildrenPrices = $additionalChildrenPrices;
        return $this;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->saveAdditionalPrices();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->saveAdditionalPrices();
    }

    private function saveAdditionalPrices(): void
    {
    }

    public function __call($name , array $arguments)
    {
        if (preg_match('/^(additionalPrice|additionalChildrenPrice){1}\d+$/iu', $name, $matches)) {
            $num = (int) preg_replace('/[^\d]+/iu', '', $name);
            $methodName = 'get' . ucfirst($matches[1]) . 's';
            if (isset($this->$methodName()[$num])) {
                return $this->$methodName()[$num];
            }
            return null;
        }

        throw new \BadMethodCallException('Method not implemented.');
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\RoomTypeCategory
     */
    public function getRoomTypeCategory()
    {
        return $this->roomTypeCategory;
    }

    /**
     * @param RoomTypeCategory|null $roomTypeCategory
     *
     * @return $this
     */
    public function setRoomTypeCategory(RoomTypeCategory $roomTypeCategory = null)
    {
        $this->roomTypeCategory = $roomTypeCategory;

        return $this;
    }

    /**
     * @param RoomTypeInterface $room
     * @param bool|false $category
     * @return $this
     */
    public function setCategoryOrRoomType(RoomTypeInterface $room, $category = false)
    {
        if ($category) {
            $this->setRoomTypeCategory($room);
        } else {
            $this->setRoomType($room);
        }

        return $this;
    }

    /**
     * @param bool|false $category
     * @return RoomTypeInterface
     */
    public function getCategoryOrRoomType($category = false)
    {
        return $category ? $this->getRoomTypeCategory() : $this->getRoomType();
    }

    /**
     * @param PriceCache $newPriceCache
     * @return bool
     */
    public function isSamePriceCaches(PriceCache $newPriceCache)
    {
        return $this->getAdditionalPrice() == $newPriceCache->getAdditionalPrice()
            && $this->getIsPersonPrice() == $newPriceCache->getIsPersonPrice()
            && $this->getPrice() == $newPriceCache->getPrice()
            && $this->getChildPrice() == $newPriceCache->getChildPrice()
            && $this->getAdditionalChildrenPrice() == $newPriceCache->getAdditionalChildrenPrice()
            && $this->getSinglePrice() == $newPriceCache->getSinglePrice()
            && $this->isDataCollectionsEqual($this->getAdditionalPrices(),
                $newPriceCache->getAdditionalPrices())
            && $this->isDataCollectionsEqual($this->getAdditionalChildrenPrices(),
                $newPriceCache->getAdditionalChildrenPrices());
    }

    /**
     * @param array $firstPriceCacheCollection
     * @param array $secondPriceCacheCollection
     * @return bool
     */
    public function isDataCollectionsEqual(array $firstPriceCacheCollection, array $secondPriceCacheCollection)
    {
        $additionalChildrenPricesDiff = array_diff($firstPriceCacheCollection, $secondPriceCacheCollection);
        if (count($additionalChildrenPricesDiff) == 0
            || (count($additionalChildrenPricesDiff) == 1 && current($additionalChildrenPricesDiff) == null)) {
            return true;
        }

        return false;
    }
}
