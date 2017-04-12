<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 * @ODM\EmbeddedDocument()
 * Class MovingPackageData
 * @package MBH\Bundle\PackageBundle\Document
 */
class MovingPackageData
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isMoved = false;

    /**
     * @var Package
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Package")
     */
    private $package;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    private $newRoomType;

    /**
     * @var RoomType
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    private $oldRoomType;

    /**
     * @var Room
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    private $oldAccommodation;

    /**
     * @return Room
     */
    public function getOldAccommodation(): ?Room
    {
        return $this->oldAccommodation;
    }

    /**
     * @param Room $oldAccommodation
     * @return MovingPackageData
     */
    public function setOldAccommodation(?Room $oldAccommodation): MovingPackageData
    {
        $this->oldAccommodation = $oldAccommodation;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getOldRoomType(): ?RoomType
    {
        return $this->oldRoomType;
    }

    /**
     * @param RoomType $oldRoomType
     * @return MovingPackageData
     */
    public function setOldRoomType(RoomType $oldRoomType): MovingPackageData
    {
        $this->oldRoomType = $oldRoomType;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMoved(): bool
    {
        return $this->isMoved;
    }

    /**
     * @param bool $isMoved
     * @return MovingPackageData
     */
    public function setIsMoved(bool $isMoved): MovingPackageData
    {
        $this->isMoved = $isMoved;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return MovingPackageData
     */
    public function setId(string $id): MovingPackageData
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * @param Package $package
     * @return MovingPackageData
     */
    public function setPackage(Package $package): MovingPackageData
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return RoomType
     */
    public function getNewRoomType(): RoomType
    {
        return $this->newRoomType;
    }

    /**
     * @param RoomType $newRoomType
     * @return MovingPackageData
     */
    public function setNewRoomType(RoomType $newRoomType): MovingPackageData
    {
        $this->newRoomType = $newRoomType;

        return $this;
    }
}