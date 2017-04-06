<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\RoomType;

/**
 * @ODM\EmbeddedDocument()
 * Class MovingPackageData
 * @package MBH\Bundle\PackageBundle\Document
 */
class MovingPackageData
{
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