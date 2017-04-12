<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
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
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_name",
     *      max=100,
     *      maxMessage="validator.document.roomType.max_name"
     * )
     * @ODM\Index()
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_name",
     *      max=100,
     *      maxMessage="validator.document.roomType.max_name"
     * )
     * @ODM\Index()
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.roomType.min_description",
     *      max=1000,
     *      maxMessage="validator.document.roomType.max_description"
     * )
     * @ODM\Index()
     */
    protected $description;
    /**
     * @var RoomTypeZip $roomTypeZip
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\ClientBundle\Document\RoomTypeZip", mappedBy="categories")
     */
    protected $roomTypeZip;

    /**
     * @var
     * @Gedmo\Versioned()
     * @ODM\Field(type="string", name="descriptionUrl")
     * @Assert\Length(
     *     min=2,
     *     max=512
     * )
     */
    protected $descriptionUrl;

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
     * @param \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     * @return $this
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;

        return $this;
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
        foreach($this->getTypes() as $roomType) {
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

    /**
     * @return mixed
     */
    public function getDescriptionUrl()
    {
        return $this->descriptionUrl;
    }

    /**
     * @param mixed $descriptionUrl
     */
    public function setDescriptionUrl($descriptionUrl)
    {
        $this->descriptionUrl = $descriptionUrl;
    }


    /**
     * @return RoomTypeZip
     */
    public function getRoomTypeZip()
    {
        return $this->roomTypeZip;
    }

    /**
     * @param RoomTypeZip $roomTypeZip
     */
    public function setRoomTypeZip(RoomTypeZip $roomTypeZip)
    {
        $this->roomTypeZip = $roomTypeZip;
    }

}
