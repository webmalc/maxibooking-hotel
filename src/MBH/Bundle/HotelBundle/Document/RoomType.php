<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="RoomTypes")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"hotel", "fullTitle"}, message="Такой тип номера уже существует")
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
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое имя",
     *      max=100,
     *      maxMessage="Слишком длинное имя"
     * )
     */
    protected $title;


    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Length(
     *      min=2,
     *      minMessage="Слишком короткое описание",
     *      max=1000,
     *      maxMessage="Слишком длинное описание"
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
     *      minMessage="HEX-код цвета должен быть не меньше шести символов",
     *      max=6,
     *      maxMessage="HEX-код цвета должен быть не больше шести символов"
     * )
     */
    protected $color = '008000';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="calculationType")
     * @Assert\NotNull()
     */
    protected $calculationType;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int(name="places")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="Количество мест не может быть меньше одного"
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
     *      minMessage="Количество мест не может быть меньше нуля"
     * )
     */
    protected $additionalPlaces = 0;

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
     * @return MBH\Bundle\HotelBundle\Document\Hotel $hotel
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
     * Set calculationType
     *
     * @param string $calculationType
     * @return self
     */
    public function setCalculationType($calculationType)
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    /**
     * Get calculationType
     *
     * @return string $calculationType
     */
    public function getCalculationType()
    {
        return $this->calculationType;
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
     * @param MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function addRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms[] = $room;
    }

    /**
     * Remove room
     *
     * @param MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function removeRoom(\MBH\Bundle\HotelBundle\Document\Room $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return Doctrine\Common\Collections\Collection $rooms
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

}
