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
     * @Assert\Regex(pattern="/^[a-zA-Z0-9 ]+$/", message="validator.document.roomType.internationalTitle.only_english")
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

    /** @ODM\EmbedMany(targetDocument="RoomTypeImage") */
    private $images = array();

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
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
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
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
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
     * Get color
     *
     * @return string $color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set places
     *
     * @param int $places
     * @return self
     */
    public function setPlaces($places)
    {
        $this->places = $places;
        return $this;
    }

    /**
     * Get places
     *
     * @return int $places
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set additionalPlaces
     *
     * @param int $additionalPlaces
     * @return self
     */
    public function setAdditionalPlaces($additionalPlaces)
    {
        $this->additionalPlaces = $additionalPlaces;
        return $this;
    }

    /**
     * Get additionalPlaces
     *
     * @return int $additionalPlaces
     */
    public function getAdditionalPlaces()
    {
        return $this->additionalPlaces;
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

    public function __construct()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return int
     */
    public function getTotalPlaces()
    {
        return $this->places + $this->additionalPlaces;
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

            if ( $i > $this->getPlaces() && $i > $adults) {
                $result['children']++;
            } else {
                $result['adults']++;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAdultsChildrenCombinations()
    {
        $result = [];

        for ($i = 1 ; $i <= $this->getTotalPlaces(); $i++) {
            $result[] = ['adults' => $i, 'children' => 0];
        }
        for ($i = $this->getPlaces(); $i <= $this->getTotalPlaces(); $i++) {
            for($k = 1; $k <= $this->getAdditionalPlaces(); $k++) {
                if(($k + $i) && ($k + $i) <= $this->getTotalPlaces()) {
                    $result[] = ['adults' => $i, 'children' => $k];
                }
            }
        }

        return $result;
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
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
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






//    public function getUploadRootDir()
//    {
//        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
//    }
//
//    public function getUploadDir()
//    {
//        return 'upload/roomTypes';
//    }
//
//    public function uploadImage(\Symfony\Component\HttpFoundation\File\UploadedFile $image = null)
//    {
//        if (empty($image)) {
//            return;
//        }
//
//        $this->image = null;
//
//        $newName = $this->id . '.'. $image->guessExtension();
//        $image->move($this->getUploadRootDir(), $newName);
//
//        $this->image = $newName;
//    }


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
     * Get roomSpace
     *
     * @return int $roomSpace
     */
    public function getRoomSpace()
    {
        return $this->roomSpace;
    }

    /**
     * Set editor
     *
     * @param string $editor
     * @return self
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
        return $this;
    }

    /**
     * Get editor
     *
     * @return string $editor
     */
    public function getEditor()
    {
        return $this->editor;
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

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection $images
     */
    public function getImages()
    {
        return $this->images;
    }

    public function getMainImage()
    {
        foreach($this->getImages() as $image) {
            if ($image->getIsMain()) {
                return $image;
            }
        }

        return null;
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
