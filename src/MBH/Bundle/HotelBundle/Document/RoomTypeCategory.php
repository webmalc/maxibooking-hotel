<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Partials\RoomTypeTrait;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;

/**
 * @ODM\Document(collection="RoomTypeCategory", repositoryClass="MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ODM\HasLifecycleCallbacks
 */

class RoomTypeCategory extends Base implements RoomTypeInterface
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;
    use RoomTypeTrait;

    /**
     * @var Hotel
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="roomTypeCategory")
     * @Assert\NotNull()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_name",
     *      max=100,
     *      maxMessage="validator.document.roomType.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_name",
     *      max=100,
     *      maxMessage="validator.document.roomType.max_name"
     * )
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_description",
     *      max=1000,
     *      maxMessage="validator.document.roomType.max_description"
     * )
     */
    protected $description;

    /**
     * @var RoomType[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="\MBH\Bundle\HotelBundle\Document\RoomType", mappedBy="category")
     */
    protected $types;

    public function __construct()
    {
        $this->types = new ArrayCollection();
    }

    /**
     * @return Hotel|null
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;

        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ArrayCollection|RoomType[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param ArrayCollection|RoomType[] $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return RoomType[]
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * @param RoomType[] $roomTypes
     */
    public function setRoomTypes($roomTypes)
    {
        $this->roomTypes = $roomTypes;
    }

    public function getIsHostel()
    {
        return false;
    }

    public function getAdditionalPlaces()
    {
        $places  = 0;

        foreach ($this->types as $roomType) {
            $places = max($roomType->getAdditionalPlaces(), $places);
        }
        return $places;
    }

    public function getMainImage()
    {
        foreach($this->getRoomTypes() as $roomType) {
            if($roomType->getMainImage()) {
                return $roomType->getMainImage();
            }
        }
        return null;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
