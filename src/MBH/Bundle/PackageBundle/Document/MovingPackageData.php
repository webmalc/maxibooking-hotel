<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\UserBundle\Document\User;

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
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    private $dateOfMove;

    /**
     * @var User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    private $movedUser;

    /**
     * @return \DateTime
     */
    public function getDateOfMove(): ?\DateTime
    {
        return $this->dateOfMove;
    }

    /**
     * @return User
     */
    public function getMovedUser(): ?User
    {
        return $this->movedUser;
    }

    /**
     * @param User $movedUser
     * @return MovingPackageData
     */
    public function setMovedUser(User $movedUser): MovingPackageData
    {
        $this->movedUser = $movedUser;

        return $this;
    }

    /**
     * @param \DateTime $dateOfMove
     * @return MovingPackageData
     */
    public function setDateOfMove(\DateTime $dateOfMove): MovingPackageData
    {
        $this->dateOfMove = $dateOfMove;

        return $this;
    }

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
     * @param User $user
     * @param bool $isMoved
     * @param bool $isSetDate
     * @return MovingPackageData
     */
    public function setMovingData(User $user, $isMoved = true, $isSetDate = true)
    {
        $this->movedUser = $user;
        $this->setIsMoved($isMoved);
        if ($isSetDate) {
            $this->setDateOfMove(new \DateTime());
        }

        return $this;
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