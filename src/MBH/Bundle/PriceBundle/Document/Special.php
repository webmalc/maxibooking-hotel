<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Tariffs", repositoryClass="MBH\Bundle\PriceBundle\Document\SpecialRepository")
 * @Gedmo\Loggable
 * @MBHValidator\Tariff
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "hotel"}, message="document.already.exists")
 * @ODM\HasLifecycleCallbacks
 */
class Special extends Base
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    
    /** 
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="specials")
     * @Assert\NotNull(message="validator.hotel.empty")
     */
    protected $hotel;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.field.short",
     *      max=100,
     *      maxMessage="validator.field.long"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.field.short",
     *      max=100,
     *      maxMessage="validator.field.long"
     * )
     */
    protected $title;
    
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.field.short",
     *      max=300,
     *      maxMessage="validator.field.long"
     * )
     */
    protected $description;


    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float", name="discount")
     * @Assert\Range(min=0.1)
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     */
    protected $discount;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isPercent")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isPercent = true;

    /**
     * @var ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Tariff")
     */
    protected $tariffs;

    /**
     * @var ArrayCollection
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomTypes;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="begin")
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="displayFrom")
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $displayFrom;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int", name="beginOffset")
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(min="0", max="100")
     */
    protected $beginOffset = 0;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="end")
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $end;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="displayTo")
     * @Assert\Date()
     * @Assert\NotNull()
     */
    protected $displayTo;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int", name="endOffset")
     * @Assert\Type(type="numeric")
     * @Assert\NotNull()
     * @Assert\Range(min="0", max="100")
     */
    protected $endOffset = 0;

    public function __construct()
    {
        $this->tariffs = new ArrayCollection();
        $this->roomTypes = new ArrayCollection();
    }

    /**
     * Set hotel
     *
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle(string $fullTitle) :self
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle(): ?string
    {
        return $this->fullTitle;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title = null): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description = null)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set begin
     *
     * @param \DateTime $begin
     * @return self
     */
    public function setBegin(\DateTime $begin)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * Get begin
     *
     * @return \DateTime $begin
     */
    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return self
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime $end
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getBeginOffset(): int
    {
        return $this->beginOffset;
    }

    /**
     * @param int $beginOffset
     * @return Special
     */
    public function setBeginOffset(int $beginOffset): Special
    {
        $this->beginOffset = $beginOffset;
        return $this;
    }

    /**
     * @return int
     */
    public function getEndOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * @param int $endOffset
     * @return Special
     */
    public function setEndOffset(int $endOffset): Special
    {
        $this->endOffset = $endOffset;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTariffs(): ArrayCollection
    {
        return $this->tariffs;
    }

    /**
     * @param ArrayCollection $tariffs
     * @return Special
     */
    public function setTariffs(ArrayCollection $tariffs): Special
    {
        $this->tariffs = $tariffs;
        return $this;
    }

    /**
     * @param Tariff $tariff
     * @return Special
     */
    public function addTariff(Tariff $tariff): self
    {
        $this->tariffs[] = $tariff;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoomTypes(): ArrayCollection
    {
        return $this->roomTypes;
    }

    /**
     * @param ArrayCollection $roomTypes
     * @return Special
     */
    public function setRoomTypes(ArrayCollection $roomTypes): Special
    {
        $this->roomTypes = $roomTypes;
        return $this;
    }

    /**
     * @param RoomType $roomType
     * @return Special
     */
    public function addRoomType(RoomType $roomType): self
    {
        $this->roomTypes[] = $roomType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDisplayFrom(): ?\DateTime
    {
        return $this->displayFrom;
    }

    /**
     * @param \DateTime $displayFrom
     * @return Special
     */
    public function setDisplayFrom(\DateTime $displayFrom): Special
    {
        $this->displayFrom = $displayFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDisplayTo(): ?\DateTime
    {
        return $this->displayTo;
    }

    /**
     * @param \DateTime $displayTo
     * @return Special
     */
    public function setDisplayTo(\DateTime $displayTo): Special
    {
        $this->displayTo = $displayTo;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return (float)$this->discount;
    }

    /**
     * @param float $discount
     * @return Special
     */
    public function setDiscount(float $discount): Special
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsPercent(): bool
    {
        return $this->isPercent;
    }

    /**
     * @param bool $isPercent
     * @return Special
     */
    public function setIsPercent(bool $isPercent): Special
    {
        $this->isPercent = $isPercent;
        return $this;
    }


}
