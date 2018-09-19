<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="RoomCache", repositoryClass="MBH\Bundle\PriceBundle\Document\RoomCacheRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @MongoDBUnique(fields={"roomType", "date", "tariff"}, message="RoomCache already exist.")
 * @ODM\Index(keys={"hotel"="asc","roomType"="asc","date"="asc"})
 */
class RoomCache extends Base
{
    use TimestampableDocument;
    use BlameableDocument;

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
     * @Assert\NotNull()
     * @ODM\Index()
     */
    protected $roomType;

    /**
     * @var \MBH\Bundle\PriceBundle\Document\Tariff
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
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
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     * @Gedmo\Versioned
     */
    protected $totalRooms;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     * @ODM\Index()
     * @Gedmo\Versioned
     */
    protected $packagesCount = 0;

    /**
     * @var int
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @ODM\Index()
     */
    protected $leftRooms;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="PackageInfo")
     */
    protected $packageInfo;

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     */
    protected $isOpen = false;

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
    public function setRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
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
     * Set totalRooms
     *
     * @param int $totalRooms
     * @return self
     */
    public function setTotalRooms($totalRooms)
    {
        $this->totalRooms = (int) $totalRooms;
        if ($this->totalRooms < 0) {
            $this->totalRooms = 0;
        }
        return $this;
    }

    /**
     * Get totalRooms
     *
     * @return int $totalRooms
     */
    public function getTotalRooms()
    {
        return $this->totalRooms;
    }

    /**
     * Set packagesCount
     *
     * @param int $packagesCount
     * @return self
     */
    public function setPackagesCount($packagesCount)
    {
        $this->packagesCount = (int) $packagesCount;
        if ($this->packagesCount < 0) {
            $this->packagesCount = 0;
        }
        return $this;
    }

    /**
     * Get packagesCount
     *
     * @return int $packagesCount
     */
    public function getPackagesCount()
    {
        return $this->packagesCount;
    }

    /**
     * @return float
     */
    public function packagesCountPercent()
    {
        return $this->totalRooms > 0 ? round(($this->packagesCount * 100)/ $this->totalRooms, 2) : 0;
    }

    /**
     * Set leftRooms
     *
     * @param int $leftRooms
     * @return self
     */
    public function setLeftRooms($leftRooms)
    {
        $this->leftRooms = (int) $leftRooms;
        return $this;
    }

    /**
     * Get leftRooms
     *
     * @return int $leftRooms
     */
    public function getLeftRooms()
    {
        return $this->leftRooms;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->calcLeftRooms();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->calcLeftRooms();
    }

    /**
     * @return $this
     */
    public function calcLeftRooms()
    {
        $this->setLeftRooms($this->getTotalRooms() - $this->getPackagesCount());

        return $this;
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

    public function __construct()
    {
        $this->packageInfo = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add packageInfo
     *
     * @param \MBH\Bundle\PriceBundle\Document\PackageInfo $packageInfo
     */
    public function addPackageInfo(\MBH\Bundle\PriceBundle\Document\PackageInfo $packageInfo)
    {
        $this->packageInfo[] = $packageInfo;
    }

    /**
     * Remove packageInfo
     *
     * @param \MBH\Bundle\PriceBundle\Document\PackageInfo $packageInfo
     */
    public function removePackageInfo(\MBH\Bundle\PriceBundle\Document\PackageInfo $packageInfo)
    {
        $this->packageInfo->removeElement($packageInfo);
    }

    /**
     * @param Tariff $tariff
     * @return mixed
     */
    public function getPackageInfo(Tariff $tariff = null)
    {
        if ($tariff == null) {
            return $this->packageInfo;
        }

        foreach ($this->packageInfo as $packageInfo) {
            if ($packageInfo->getTariff()->getId() == $tariff->getId()) {
                return $packageInfo;
            }
        }
        return null;
    }

    public function getPackageCountByTariff(Tariff $tariff)
    {
        $packageInfo = $this->getPackageInfo($tariff);

        if ($packageInfo) {
            return $packageInfo->getPackagesCount();
        }

        return 0;
    }

    public function soldRefund(Tariff $tariff, $refund = false)
    {
        $newPackageInfo = $this->getPackageInfo($tariff);

        if (!$newPackageInfo) {
            $newPackageInfo = new PackageInfo();
            $newPackageInfo->setTariff($tariff)->setPackagesCount(0);
        }

        if ($refund) {
            $newPackageInfo->refund();
        } else {
            $newPackageInfo->sold();
        }

        $result = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->getPackageInfo() as $packageInfo) {
            if ($newPackageInfo->getTariff()->getId() == $packageInfo->getTariff()->getId()) {
                continue;
            }
            $result[] = $packageInfo;
        }
        $result[] = $newPackageInfo;

        $this->packageInfo = $result;

        return $this;
    }

    public function addPackage() {

        if (!is_numeric($this->packagesCount)) {
            $this->packagesCount = 0;
        }
        $this->packagesCount++;

        return $this;
    }

    public function removePackage() {
        if (!is_numeric($this->packagesCount)) {
            $this->packagesCount = 0;
        }
        $this->packagesCount--;

        return $this;
    }

    /**
     * Set isClosed
     *
     * @param boolean $isOpen
     * @return self
     */
    public function setIsOpen($isOpen)
    {
        $this->isOpen = $isOpen;
        return $this;
    }

    /**
     * Get isClosed
     *
     * @return boolean $isClosed
     */
    public function isOpen()
    {
        return $this->isOpen;
    }
}
