<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\InternableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\LocalizableTrait;
use MBH\Bundle\HotelBundle\Document\Partials\RoomTypeTrait;
use MBH\Bundle\HotelBundle\Model\RoomTypeInterface;
use Symfony\Component\Validator\Constraints as Assert;
use MBH\Bundle\BaseBundle\Lib\Disableable as Disableable;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @ODM\Document(collection="RoomTypes", repositoryClass="MBH\Bundle\HotelBundle\Document\RoomTypeRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "hotel"}, message="mbhhotelbundle.document.roomtype.takoy.tip.nomera.uzhe.sushchestvuyet")
 * @Disableable\Disableable
 * @ODM\HasLifecycleCallbacks
 */
class RoomType extends Base implements RoomTypeInterface
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

    use RoomTypeTrait;
    use InternableDocument;
    use LocalizableTrait;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @var Hotel
     * @ODM\ReferenceOne(targetDocument="Hotel", inversedBy="roomTypes")
     * @Assert\NotNull(message="validator.document.roomType.hotel_in_not_select")
     * @ODM\Index()
     */
    protected $hotel;

    /** @ODM\ReferenceMany(targetDocument="Room", mappedBy="roomType" ) */
    protected $rooms;

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
     * @Gedmo\Translatable()
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
     * @Gedmo\Translatable()
     */
    protected $description;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="color")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=6,
     *      minMessage="validator.document.roomType.min_hex_code",
     *      max=7,
     *      maxMessage="validator.document.roomType.max_hex_code"
     * )
     */
    protected $color = '008000';

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer(name="places")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="validator.document.roomType.min_places_amount"
     * )
     * @ODM\Index()
     */
    protected $places = 1;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer(name="additionalPlaces")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.roomType.places_amount_less_zero",
     *      max=10
     * )
     * @ODM\Index()
     */
    protected $additionalPlaces = 0;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer(name="maxInfants")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage="validator.document.roomType.places_amount_less_zero",
     *      max=6
     * )
     */
    protected $maxInfants = 3;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="roomSpace")
     * @ODM\Index()
     */
    protected $roomSpace;

    /**
     * @var string
     * @deprecated
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $image;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isHostel = false;
    /**
     * @ODM\EmbedMany(targetDocument="RoomTypeImage")
     */
    private $images = [];

    /**
     * @var \Doctrine\Common\Collections\Collection|Image[]
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\BaseBundle\Document\Image", cascade={"persist"})
     */
    protected $onlineImages;
    /**
     * @var TaskSettings
     * @ODM\EmbedOne(targetDocument="TaskSettings")
     */
    private $taskSettings;
    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    protected $facilities = [];
    /**
     * @var RoomTypeCategory|null
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\RoomTypeCategory", inversedBy="types")
     */
    protected $category;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isSmoking = false;

    /**
     * @var array
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomViewType")
     */
    protected $roomViewsTypes;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->roomViewsTypes = new ArrayCollection();
        $this->onlineImages = new ArrayCollection();
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
     * Get color
     *
     * @return string $color
     */
    public function getColor()
    {
        if ($this->color[0] != '#') {
            $this->color = '#' . $this->color;
        }

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
     * @param boolean $useCategories
     * @return array
     */
    public function getAdultsChildrenCombination($adults, $children, $useCategories = false)
    {
        $useCategories ? $isChildPrices = $this->getCategory()->getIsChildPrices() : $isChildPrices = $this->getIsChildPrices();

        if ($isChildPrices) {
            return ['adults' => $adults, 'children' => $children];
        }

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
     * @param boolean $useCategories
     * @return array
     */
    public function getAdultsChildrenCombinations($useCategories = false)
    {
        $result = [];
        $useCategories ? $isChildPrices = $this->getCategory()->getIsChildPrices() : $isChildPrices = $this->getIsChildPrices();

        $total = $this->getTotalPlaces();
        $isChildPrices ? $additional = $this->getTotalPlaces() : $additional = $this->getAdditionalPlaces();
        $isChildPrices ? $places = 1 : $places = $this->getPlaces();

        for ($i = 1; $i <= $total; $i++) {
            $result[] = ['adults' => $i, 'children' => 0];
        }
        for ($i = $places; $i <= $total; $i++) {
            for ($k = 1; $k <= $additional; $k++) {
                if (($k + $i) && ($k + $i) <= $total) {
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

    public function getMainImage()
    {
        /** @var Image $image */
        foreach ($this->onlineImages as $image) {
            if ($image->isMain()) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Get images
     * @deprecated use
     * @return \Doctrine\Common\Collections\Collection|RoomTypeImage[] $images
     */
    public function getImages()
    {
        return $this->images;
    }

    public function makeMainImageById($imageId)
    {
        foreach ($this->getOnlineImages() as $onlineImage) {
            $onlineImage->setIsMain($onlineImage->getId() == $imageId);
        }
    }

    /**
     * @return $this
     */
    public function makeFirstImageAsMain()
    {
        if (count($this->onlineImages)) {
            foreach ($this->onlineImages as $onlineImage) {
                $onlineImage->setIsMain(false);
            }

            $this->onlineImages->first()->setIsMain(true);
        }

        return $this;
    }

    public function makeMainImage(Image $image)
    {
        foreach ($this->getOnlineImages() as $onlineImage) {
            $onlineImage->setIsMain($onlineImage == $image);
        }
    }

    /**
     * @return TaskSettings|null
     */
    public function getTaskSettings()
    {
        return $this->taskSettings;
    }

    /**
     * @param TaskSettings $taskSettings
     */
    public function setTaskSettings(TaskSettings $taskSettings = null)
    {
        $this->taskSettings = $taskSettings;
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @param array $facilities
     * @return $this
     */
    public function setFacilities($facilities)
    {
        $this->facilities = $facilities;
        return $this;
    }

    /**
     * Get image
     *
     * @return string $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return RoomTypeCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param RoomTypeCategory|null $category
     * @return $this
     */
    public function setCategory(RoomTypeCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsSmoking(): ?bool
    {
        return $this->isSmoking;
    }

    /**
     * @param bool $isSmoking
     * @return RoomType
     */
    public function setIsSmoking(bool $isSmoking): RoomType
    {
        $this->isSmoking = $isSmoking;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoomViewsTypes()
    {
        return $this->roomViewsTypes;
    }

    /**
     * @param array $roomViewsTypes
     * @return RoomType
     */
    public function setRoomViewsTypes(array $roomViewsTypes): RoomType
    {
        $this->roomViewsTypes = $roomViewsTypes;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Image[]
     */
    public function getOnlineImages()
    {
        return $this->onlineImages;
    }

    /**
     * @param Image $onlineImage
     * @internal param \Doctrine\Common\Collections\Collection|Image[] $onlineImages
     */
    public function addOnlineImage(Image $onlineImage)
    {
        $this->onlineImages->add($onlineImage);
    }

    public function removeOnlineImage(Image $onlineImage)
    {
        $this->onlineImages->removeElement($onlineImage);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxInfants(): int
    {
        return $this->maxInfants;
    }

    /**
     * @param int $maxInfants
     * @return RoomType
     */
    public function setMaxInfants(int $maxInfants): RoomType
    {
        $this->maxInfants = $maxInfants;

        return $this;
    }



    /**
     * @return array
     */
    public function getOnlineImagesByPriority(): array
    {
        $result = [];
        $images = $this->onlineImages;
        if (count($images)) {
            $result = $images->toArray();
            uasort($result,
                function ($imageA, $imageB) {
                    /** @var Image $imageA */
                    /** @var Image $imageB */
                    $priorityA = $imageA->getPriority();
                    $priorityB = $imageB->getPriority();
                    if ($priorityA === $priorityB) {
                        return $imageA->getCreatedAt() <=> $imageB->getCreatedAt();
                    }

                    return $imageA->getPriority() <=> $imageB->getPriority();
                });
        }

        return $result;
    }

    /**
     * @param UploaderHelper $helper
     * @param CacheManager $cacheManager
     * @return array
     */
    public function getRoomTypePhotoData(UploaderHelper $helper, CacheManager $cacheManager)
    {
        $imagesData = [];
        /** @var Image $image */
        foreach ($this->getOnlineImages() as $image) {
            $roomTypeImageData = ['isMain' => $image->getIsDefault()];
            $roomTypeImageData['url'] = $cacheManager->getBrowserPath($helper->asset($image, 'imageFile'), 'scaler');
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = (int)$image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = (int)$image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
    }

    /**
     * @param bool $isFull
     * @param UploaderHelper|null $helper
     * @param CacheManager $cacheManager
     * @return array
     */
    public function getJsonSerialized($isFull = false, UploaderHelper $helper = null, CacheManager $cacheManager = null)
    {
        $data = [
            'id' => $this->getId(),
            'isEnabled' => $this->getIsEnabled(),
            'hotel' => $this->getHotel()->getId(),
            'title' => $this->getFullTitle() ? $this->getFullTitle() : $this->getInternationalTitle(),
            'internalTitle' => $this->getTitle(),
            'description' => $this->getDescription() ?? '',
            'numberOfPlaces' => $this->getPlaces(),
            'numberOfAdditionalPlaces' => $this->getAdditionalPlaces(),
            'places' => $this->getPlaces(),
            'additionalPlaces' => $this->getAdditionalPlaces()
        ];
        if ($isFull) {
            $comprehensiveData = [
                'isSmoking' => $this->isIsSmoking(),
                'isHostel' => $this->getIsHostel(),
                'facilities' => $this->getFacilities(),
            ];
            if ($this->getRoomSpace()) {
                $comprehensiveData['roomSpace'] = $this->getRoomSpace();
            }
            if (!is_null($helper)) {
                $comprehensiveData['photos'] = $this->getRoomTypePhotoData($helper, $cacheManager);
            }
            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}
