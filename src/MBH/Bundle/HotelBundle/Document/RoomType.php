<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="RoomTypes", repositoryClass="MBH\Bundle\HotelBundle\Document\RoomTypeRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "hotel"}, message="Такой тип номера уже существует")
 *
 * @ODM\HasLifecycleCallbacks
 */
class RoomType extends Base
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
     * @ODM\ReferenceOne(targetDocument="Hotel", inversedBy="roomTypes")
     * @Assert\NotNull(message="Не выбран отель")
     */
    protected $hotel;

    /** @ODM\ReferenceMany(targetDocument="Room", mappedBy="roomType") */
    protected $rooms;

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
     * @ODM\String()
     * @Assert\Regex(pattern="/^[^А-Яа-я]+$/iu", message="validator.document.roomType.internationalTitle.only_english")
     */
    protected $internationalTitle;


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
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="color")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.document.roomType.min_hex_code",
     *      max=6,
     *      maxMessage="validator.document.roomType.max_hex_code"
     * )
     */
    protected $color = '008000';

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="places")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="validator.document.roomType.min_places_amount"
     * )
     */
    protected $places = 1;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="additionalPlaces")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.roomType.places_amount_less_zero"
     * )
     */
    protected $additionalPlaces = 0;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="roomSpace")
     */
    protected $roomSpace;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $image;
    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isHostel = false;
    /** @ODM\EmbedMany(targetDocument="RoomTypeImage") */
    private $images = array();

    public function __construct()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return mixed
     */
    public function getInternationalTitle()
    {
        return $this->internationalTitle;
    }

    /**
     * @param mixed $internationalTitle
     */
    public function setInternationalTitle($internationalTitle)
    {
        $this->internationalTitle = $internationalTitle;
    }

    /**
     * Get color
     *
     * @return string $color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (!empty($this->title)) {
            return $this->title;
        }
        return $this->fullTitle;
    }

    /**
     * Add room
     *
     * @param \MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function addRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param \MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function removeRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return \Doctrine\Common\Collections\Collection|Room[] $rooms
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Convert children to adults
     * @param $adults
     * @param $children
     * @return array
     */
    public function getAdultsChildrenCombination($adults, $children)
    {
        $result = ['adults' => 0, 'children' => 0];
        $total = $children + $adults;

        for ($i = 1; $i <= $total; $i++) {

            if ($i > $this->getTotalPlaces()) {
                break;
            }

            if ($i > $this->getPlaces() && $i > $adults) {
                $result['children']++;
            } else {
                $result['adults']++;
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getTotalPlaces()
    {
        return $this->getPlaces() + $this->getAdditionalPlaces();
    }

    /**
     * Get places
     *
     * @return int $places
     */
    public function getPlaces()
    {
        return $this->getIsHostel() ? 1 : $this->places;
    }

    /**
     * Set places
     *
     * @param int $places
     * @return self
     */
    public function setPlaces($places)
    {
        $this->getIsHostel() ? $this->places = 1 : $this->places = $places;

        return $this;
    }
    
    /**
     * Get isHostel
     *
     * @return boolean $isHostel
     */
    public function getIsHostel()
    {
        return $this->isHostel;
    }

    /**
     * Get additionalPlaces
     *
     * @return int $additionalPlaces
     */
    public function getAdditionalPlaces()
    {
        return $this->getIsHostel() ? 0 : $this->additionalPlaces;
    }

    /**
     * Set additionalPlaces
     *
     * @param int $additionalPlaces
     * @return self
     */
    public function setAdditionalPlaces($additionalPlaces)
    {
        $this->getIsHostel() ? $this->additionalPlaces = 0 : $this->additionalPlaces = $additionalPlaces;

        return $this;
    }

    /**
     * Set isHostel
     *
     * @param boolean $isHostel
     * @return self
     */
    public function setIsHostel($isHostel)
    {
        $this->isHostel = $isHostel;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdultsChildrenCombinations()
    {
        $result = [];

        for ($i = 1; $i <= $this->getTotalPlaces(); $i++) {
            $result[] = ['adults' => $i, 'children' => 0];
        }
        for ($i = $this->getPlaces(); $i <= $this->getTotalPlaces(); $i++) {
            for ($k = 1; $k <= $this->getAdditionalPlaces(); $k++) {
                if (($k + $i) && ($k + $i) <= $this->getTotalPlaces()) {
                    $result[] = ['adults' => $i, 'children' => $k];
                }
            }
        }

        return $result;
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
     * Set image
     *
     * @param string $image
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Get roomSpace
     *
     * @return int $roomSpace
     */
    public function getRoomSpace()
    {
        return $this->roomSpace;
    }

    /**
     * Set roomSpace
     *
     * @param int $roomSpace
     * @return self
     */
    public function setRoomSpace($roomSpace)
    {
        $this->roomSpace = $roomSpace;
        return $this;
    }

    /**
     * Add image
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomTypeImage $image
     */
    public function addImage(\MBH\Bundle\HotelBundle\Document\RoomTypeImage $image)
    {
        $this->images[] = $image;
    }

    /**
     * Remove image
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomTypeImage $image
     */
    public function removeImage(\MBH\Bundle\HotelBundle\Document\RoomTypeImage $image)
    {
        $this->images->removeElement($image);
    }

    public function getMainImage()
    {
        foreach ($this->getImages() as $image) {
            if ($image->getIsMain()) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @ODM\PreUpdate()
     */
    public function preUpdate()
    {
        if(!$this->internationalTitle && $this->fullTitle) {
            $this->internationalTitle = Helper::translateToLat($this->fullTitle);
        }
    }


    public function deleteImageById(RoomType $entity,$imageId){
        $result = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($entity->getImages() as $element) {
            if ($element->getId() == $imageId) {
                $imagePath = $element->getPath();
                if (file_exists($imagePath) && is_readable($imagePath)) {
                    unlink($imagePath);
                }
            } else {
                $result[] = $element;
            }
        }
        $entity->images = $result;
    }

    public function makeMainImageById(RoomType $entity, $imageId){
        foreach($entity->getImages() as $element) {
            if ($element->getId() == $imageId) {
                /* @var $element \MBH\Bundle\HotelBundle\Document\RoomTypeImage */
                $element->setIsMain(true);
            } else {
                $element->setIsMain(false);
            }
        }
    }
}
