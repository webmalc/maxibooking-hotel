<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Traits\NoteTrait;
use MBH\Bundle\BaseBundle\Document\Traits\PackageTrait;
use MBH\Bundle\HotelBundle\Document\Room;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\PackageBundle\Validator\Constraints as MBHConstraints;


/**
 * @ODM\Document(collection="PackageAccommodation")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 * @MBHConstraints\PackageAccommodations()
 */

class PackageAccommodation extends Base implements \JsonSerializable
{
    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    use NoteTrait;

    use PackageTrait;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\NotNull(message="not_null")
     * @Assert\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
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
        $this->toArray();
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