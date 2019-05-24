<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\NoteTrait;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\PackageBundle\Validator\Constraints as MBHConstraints;


/**
 * @ODM\Document(collection="PackageAccommodation", repositoryClass="MBH\Bundle\PackageBundle\Document\PackageAccommodationRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MBHConstraints\PackageAccommodations()
 * @ODM\Index(keys={"begin"="asc","end"="asc"})
 */

class PackageAccommodation extends Base implements \JsonSerializable
{
    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    use NoteTrait;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="not_null")
     * @Assert\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="not_null")
     * @Assert\Date()
     */
    protected $end;

    /**
     * @var Room
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     * @Assert\NotNull(message="not_null")
     */
    protected $accommodation;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Assert\NotNull
     */
    protected $isAutomaticallyChangeable = true;

    /**
     * this field used only for validator
     * @var Package
     */
    protected $packageForValidator;

    /**
     * @param Package $package
     * @return $this
     */
    public function setPackageForValidator(Package $package)
    {
        $this->packageForValidator = $package;

        return $this;
    }

    /**
     * @return Package
     */
    public function getPackageForValidator(): ?Package
    {
        return $this->packageForValidator;
    }

    /**
     * @return bool
     */
    public function isAutomaticallyChangeable(): ?bool
    {
        return $this->isAutomaticallyChangeable;
    }

    /**
     * @param bool $isAutomaticallyChangeable
     * @return PackageAccommodation
     */
    public function setIsAutomaticallyChangeable(bool $isAutomaticallyChangeable): PackageAccommodation
    {
        $this->isAutomaticallyChangeable = $isAutomaticallyChangeable;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return RoomType
     */
    public function getRoomType()
    {
        return $this->accommodation->getRoomType();
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->accommodation->getHotel();
    }

    public function getFloor()
    {
        return $this->accommodation->getFloor();
    }

    public function getHousing()
    {
        return $this->accommodation->getHousing();
    }

    public function getStatus()
    {
        return $this->accommodation->getStatus();
    }

    public function getFacilities()
    {
        return $this->accommodation->getFacilities();
    }

    public function getTitle()
    {
        return $this->accommodation->getTitle();
    }

    public function getFullTitle()
    {
        return $this->accommodation->getFullTitle();
    }

    public function getIsSmoking()
    {
        return $this->accommodation->getIsSmoking();
    }

    public function getRoomViewTypes()
    {
        return $this->accommodation->getRoomViewsTypes();
    }

    /**
     * @return \DateTime
     */
    public function getBegin(): \DateTime
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     * @return PackageAccommodation
     */
    public function setBegin(\DateTime $begin = null): PackageAccommodation
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return PackageAccommodation
     */
    public function setEnd(\DateTime $end = null): PackageAccommodation
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return Room
     */
    public function getAccommodation(): Room
    {
        return $this->accommodation;
    }

    /**
     * @param Room $accommodation
     * @return PackageAccommodation
     */
    public function setAccommodation(Room $accommodation): self
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->getAccommodation();
    }

    /**
     * @param Room $accommodation
     * @return PackageAccommodation
     */
    public function setRoom(Room $accommodation): self
    {
        return $this->setAccommodation($accommodation);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->accommodation->getName();
    }
}
