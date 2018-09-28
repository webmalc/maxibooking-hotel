<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\InvalidateInterface;
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
 */
class PriceCache extends Base implements InvalidateInterface
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
     * @var int
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
     * @Assert\NotNull()
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
    public function setPrice($price)
    {
        $this->price = (float) $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set isPersonPrice
     *
     * @param boolean $isPersonPrice
     * @return self
     */
    public function setIsPersonPrice($isPersonPrice)
    {
        $this->isPersonPrice = (boolean) $isPersonPrice;
        return $this;
    }

    /**
     * Get isPersonPrice
     *
     * @return boolean $isPersonPrice
     */
    public function getIsPersonPrice()
    {
        return $this->isPersonPrice;
    }

    /**
     * Set additionalPrice
     *
     * @param int $additionalPrice
     * @return self
     */
    public function setAdditionalPrice($additionalPrice)
    {
        if ($additionalPrice != '' && !is_null($additionalPrice)) {
            $this->additionalPrice = (float) $additionalPrice;
        } else {
            $this->additionalPrice = null;
        }

        return $this;
    }

    /**
     * Get additionalPrice
     *
     * @return int $additionalPrice
     */
    public function getAdditionalPrice()
    {
        return $this->additionalPrice;
    }

    /**
     * Set additionalChildrenPrice
     *
     * @param int $additionalChildrenPrice
     * @return self
     */
    public function setAdditionalChildrenPrice($additionalChildrenPrice)
    {
        if ($additionalChildrenPrice != '' && !is_null($additionalChildrenPrice)) {
            $this->additionalChildrenPrice = (float) $additionalChildrenPrice;
        } else {
            $this->additionalChildrenPrice = null;
        }

        return $this;
    }

    /**
     * Get additionalChildrenPrice
     *
     * @return int $additionalChildrenPrice
     */
    public function getAdditionalChildrenPrice()
    {
        return $this->additionalChildrenPrice;
    }

    /**
     * Set singlePrice
     *
     * @param int $singlePrice
     * @return self
     */
    public function setSinglePrice($singlePrice)
    {
        if (is_numeric($singlePrice)) {
            $singlePrice = (float) $singlePrice;
        }

        $this->singlePrice = $singlePrice;

        return $this;
    }

    /**
     * Get singlePrice
     *
     * @return int $singlePrice
     */
    public function getSinglePrice()
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
     * @return array
     */
    public function getAdditionalPrices()
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
     * @return array
     */
    public function getAdditionalChildrenPrices()
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

    private function saveAdditionalPrices()
    {
        $this->additionalPrices = [0 => $this->getAdditionalPrice()] + $this->additionalPrices;
        $this->additionalChildrenPrices = [0 => $this->getAdditionalChildrenPrice()] + $this->additionalChildrenPrices;

        foreach ($this->additionalPrices as $key => $price) {
            if ($price != '' && !is_null($price)) {
                $this->additionalPrices[$key] = (float) $price;
            } else {
                $this->additionalPrices[$key] = null;
            }
        }
        foreach ($this->additionalChildrenPrices as $key => $price) {
            if ($price != '' && !is_null($price)) {
                $this->additionalChildrenPrices[$key] = (float) $price;
            } else {
                $this->additionalChildrenPrices[$key] = null;
            }
        }
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
            && $this->isDataCollectionsEqual($newPriceCache->getAdditionalChildrenPrices(),
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

        return true;
    }
}
