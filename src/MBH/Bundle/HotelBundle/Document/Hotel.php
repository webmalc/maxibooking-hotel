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
use MBH\Bundle\CashBundle\Document\CardType;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @ODM\Document(collection="Hotels", repositoryClass="MBH\Bundle\HotelBundle\Document\HotelRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="fullTitle", message="validator.document.hotel.hotel_is_exist")
 * @ODM\HasLifecycleCallbacks
 */
class Hotel extends Base implements \JsonSerializable, AddressInterface
{
    const DEFAULT_ARRIVAL_TIME = 14;
    const DEFAULT_DEPARTURE_TIME = 12;

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
    use InternableDocument;
    use LocalizableTrait;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.max_name"
     * )
     * @ODM\Index()
     * @Gedmo\Translatable
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.min_name"
     * )
     * @ODM\Index()
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="prefix")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_prefix",
     *      max=100,
     *      maxMessage="validator.document.hotel.max_prefix"
     * )
     * @ODM\Index()
     */
    protected $prefix;

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
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isDefault = false;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric", message= "validator.document.hotel.wrong_latitude")
     * @ODM\Index()
     */
    protected $latitude;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric", message="validator.document.hotel.wrong_longitude")
     * @ODM\Index()
     */
    protected $longitude;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Field(type="collection")
     */
    protected $type = [];

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Field(type="collection")
     */
    protected $theme = [];

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Field(type="collection")
     */
    protected $facilities = [];

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1,
     *      minMessage="validator.document.hotel.min_stars",
     *      max=5,
     *      maxMessage="validator.document.hotel.max_stars",
     * )
     */
    protected $rating;

    /** @ODM\ReferenceMany(targetDocument="RoomType", mappedBy="hotel") */
    protected $roomTypes;

    /** @ODM\ReferenceMany(targetDocument="RoomTypeCategory", mappedBy="hotel") */
    protected $roomTypesCategories;

    /** @ODM\ReferenceMany(targetDocument="Room", mappedBy="hotel") */
    protected $rooms;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff", mappedBy="hotel") */
    protected $tariffs;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\Special", mappedBy="hotel") */
    protected $specials;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\ServiceCategory", mappedBy="hotel") */
    protected $servicesCategories;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig", mappedBy="hotel") */
    protected $vashotelConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig", mappedBy="hotel") */
    protected $oktogoConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\BookingConfig", mappedBy="hotel") */
    protected $bookingConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig", mappedBy="hotel") */
    protected $hotelinnConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig", mappedBy="hotel") */
    protected $ostrovokConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig", mappedBy="hotel") */
    protected $myallocatorConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig", mappedBy="hotel") */
    protected $expediaConfig;

    /** @ODM\ReferenceOne(targetDocument="MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig", mappedBy="hotel") */
    protected $hundredOneHotelsConfig;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\IngredientCategory", mappedBy="hotel") */
    protected $ingredientCategories;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\TableType", mappedBy="hotel") */
    protected $TableTypes;


    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\RestaurantBundle\Document\DishMenuCategory", mappedBy="hotel") */
    protected $dishMenuCategories;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $countryTld;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     */
    protected $regionId;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     */
    protected $cityId;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Gedmo\Translatable()
     */
    protected $settlement;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     * @Gedmo\Translatable()
     */
    protected $street;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $internationalStreetName;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $house;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $corpus;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $flat;

    /**
     * @ODM\Field(type="string")
     * @Gedmo\Versioned()
     */
    protected $zipCode;

    /**
     * @var Image
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\BaseBundle\Document\Image", cascade={"persist"})
     */
    protected $logoImage;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\BaseBundle\Document\Image")
     */
    protected $images;

    /**
     * @var Housing[]
     * @ODM\ReferenceMany(targetDocument="Housing", mappedBy="hotel")
     */
    protected $housings;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="2M", mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     * }, mimeTypesMessage="validator.document.OrderDocument.file_type")
     */
    protected $file;

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    protected $logo;

    /**
     * @var Organization|null
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization", mappedBy="hotels")
     */
    protected $organization;

    /**
     * @ODM\Field(type="string")
     * @var string
     * @Gedmo\Translatable
     */
    protected $description;

    /**
     * @var ContactInfo
     * @Assert\Valid()
     * @ODM\EmbedOne(targetDocument="ContactInfo")
     */
    protected $contactInformation;

    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    protected $supportedLanguages = [];

    /**
     * @var array
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CardType")
     */
    protected $acceptedCardTypes;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isInvoiceAccepted = true;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $checkinoutPolicy;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $smokingPolicy;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\Type(type="int")
     * @Assert\Range(max="23", min="0")
     */
    protected $packageArrivalTime = self::DEFAULT_ARRIVAL_TIME;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\Type(type="int")
     * @Assert\Range(max="23", min="0")
     */
    protected $packageDepartureTime = self::DEFAULT_DEPARTURE_TIME;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $aboutLink;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $roomsLink;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $mapLink;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $contactsLink;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $pollLink;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    public function __construct()
    {
        $this->roomTypes = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->tariffs = new ArrayCollection();
        $this->specials = new ArrayCollection();
        $this->dishMenuCategories = new ArrayCollection();
        $this->ingredientCategories = new ArrayCollection();
        $this->TableTypes = new ArrayCollection();
        $this->acceptedCardTypes = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getTableTypes()
    {
        return $this->TableTypes;
    }

    /**
     * @param mixed $TableTypes
     */
    public function setTableTypes($TableTypes)
    {
        $this->TableTypes = $TableTypes;
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
     * @return string
     */
    public function getFullTitleOrTitle()
    {
        return $this->getFullTitle() ?? $this->getTitle();
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
     * Get prefix
     *
     * @return string $prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Add roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function addRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomTypes[] = $roomType;
    }

    /**
     * Remove roomType
     *
     * @param \MBH\Bundle\HotelBundle\Document\RoomType $roomType
     */
    public function removeRoomType(\MBH\Bundle\HotelBundle\Document\RoomType $roomType)
    {
        $this->roomTypes->removeElement($roomType);
    }

    /**
     * Get roomTypes
     *
     * @return RoomType[]|\Doctrine\Common\Collections\Collection $roomTypes
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
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
     * @return \Doctrine\Common\Collections\Collection $rooms
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Add tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function addTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariffs[] = $tariff;
    }

    /**
     * Remove tariff
     *
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     */
    public function removeTariff(\MBH\Bundle\PriceBundle\Document\Tariff $tariff)
    {
        $this->tariffs->removeElement($tariff);
    }

    /**
     * Get tariffs
     *
     * @return \Doctrine\Common\Collections\Collection $tariffs
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }

    /**
     * Get vashotelConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig $vashotelConfig
     */
    public function getVashotelConfig()
    {
        return $this->vashotelConfig;
    }

    /**
     * Set vashotelConfig
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig $vashotelConfig
     * @return self
     */
    public function setVashotelConfig(\MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig $vashotelConfig)
    {
        $this->vashotelConfig = $vashotelConfig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHundredOneHotelsConfig()
    {
        return $this->hundredOneHotelsConfig;
    }

    /**
     * @param mixed $hundredOneHotelsConfig
     * @return $this
     */
    public function setHundredOneHotelsConfig(HundredOneHotelsConfig $hundredOneHotelsConfig)
    {
        $this->hundredOneHotelsConfig = $hundredOneHotelsConfig;
        return $this;
    }

    /**
     * Get oktogoConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig $oktogoConfig
     */
    public function getOktogoConfig()
    {
        return $this->oktogoConfig;
    }

    /**
     * Set oktogoConfig
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig $oktogoConfig
     * @return self
     */
    public function setOktogoConfig(\MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig $oktogoConfig)
    {
        $this->oktogoConfig = $oktogoConfig;

        return $this;
    }

    /**
     * Add servicesCategory
     *
     * @param ServiceCategory $servicesCategory
     */
    public function addServicesCategory(ServiceCategory $servicesCategory)
    {
        $this->servicesCategories[] = $servicesCategory;
    }

    /**
     * Remove servicesCategory
     *
     * @param ServiceCategory $servicesCategory
     */
    public function removeServicesCategory(ServiceCategory $servicesCategory)
    {
        $this->servicesCategories->removeElement($servicesCategory);
    }

    /**
     * Get servicesCategories
     *
     * @return ServiceCategory[]|\Doctrine\Common\Collections\Collection $servicesCategories
     */
    public function getServicesCategories()
    {
        return $this->servicesCategories;
    }

    /**
     * Get services list
     * @param boolean $online
     * @param boolean $enabled
     * @return array
     */
    public function getServices($enabled = false, $online = false)
    {
        $result = [];

        foreach ($this->servicesCategories as $serviceCategory) {
            /** @var Service $service */
            foreach ($serviceCategory->getServices() as $service) {
                if ($online && (!$service->getIsOnline() || !in_array($service->getCalcType(), ['per_stay', 'not_applicable']))) {
                    continue;
                }
                if ($enabled && !$service->getIsEnabled()) {
                    continue;
                }
                if ($service->getPrice() !== null) {
                    $result[] = $service;
                }
            }
        }

        return $result;
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
     * Get country
     *
     * @return string
     */
    public function getCountryTld()
    {
        return $this->countryTld;
    }

    /**
     * Set country
     *
     * @param string $countryTld
     * @return self
     */
    public function setCountryTld($countryTld)
    {
        $this->countryTld = $countryTld;

        return $this;
    }

    /**
     * Get region
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * Set region
     *
     * @param int $regionId
     * @return self
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * @param mixed $settlement
     */
    public function setSettlement($settlement)
    {
        $this->settlement = $settlement;
    }

    /**
     * Get latitude
     *
     * @return float $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get type
     *
     * @return collection $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param collection $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get theme
     *
     * @return collection $theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set theme
     *
     * @param collection $theme
     * @return self
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get facilities
     *
     * @return collection $facilities
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * Set facilities
     *
     * @param collection $facilities
     * @return self
     */
    public function setFacilities($facilities)
    {
        $this->facilities = $facilities;

        return $this;
    }

    /**
     * Get rating
     *
     * @return int $rating
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rating
     *
     * @param int $rating
     * @return self
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get bookingConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\BookingConfig $bookingConfig
     */
    public function getBookingConfig()
    {
        return $this->bookingConfig;
    }

    /**
     * Set bookingConfig
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\BookingConfig $bookingConfig
     * @return self
     */
    public function setBookingConfig(\MBH\Bundle\ChannelManagerBundle\Document\BookingConfig $bookingConfig)
    {
        $this->bookingConfig = $bookingConfig;

        return $this;
    }

    /**
     * Get hotelinnConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig
     */
    public function getHotelinnConfig()
    {
        return $this->hotelinnConfig;
    }

    /**
     * Set hotelinnConfig
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig $hotelinnConfig
     * @return self
     */
    public function setHotelinnConfig(\MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig $hotelinnConfig)
    {
        $this->hotelinnConfig = $hotelinnConfig;
        return $this;
    }

    /**
     * Get ostrovokConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig
     */
    public function getOstrovokConfig()
    {
        return $this->ostrovokConfig;
    }

    /**
     * Set ostrovokConfig
     *
     * @param \MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig $ostrovokConfig
     * @return self
     */
    public function setOstrovokConfig(\MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig $ostrovokConfig)
    {
        $this->ostrovokConfig = $ostrovokConfig;

        return $this;
    }

    /**
     * Get city
     *
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set city
     *
     * @param $cityId
     * @return self
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return self
     */
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     * @return self
     */
    public function setHouse($house)
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return string
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param string $corpus
     */
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;
    }

    /**
     * @return mixed
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param mixed $flat
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;
    }


    /**
     * @return mixed
     */
    public function getExpediaConfig()
    {
        return $this->expediaConfig;
    }

    /**
     * @param mixed $expediaConfig
     * @return $this
     */
    public function setExpediaConfig($expediaConfig)
    {
        $this->expediaConfig = $expediaConfig;

        return $this;
    }


    /**
     * @return Housing[]
     */
    public function getHousings()
    {
        return $this->housings;
    }

    /**
     * @param Housing[] $$housings
     */
    public function setHousings(array $housings)
    {
        $this->housings = $housings;
    }

    public function addHousing(Housing $housing)
    {
        $this->housings[] = $housing;
    }

    public function getLogoUrl()
    {
        if ($this->getFile() instanceof File) {
            return '/upload/hotelLogos/' . $this->getLogo();
        }

        return null;
    }

    /**
     * @return File|null
     */
    public function getFile()
    {
        if (!$this->file && $this->logo && is_file($this->getPath())) {
            $this->file = new File($this->getPath());
        }

        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if ($this->file) {
            $this->logo = $file->getClientOriginalName();
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getLogo();
    }

    /**
     * The absolute directory path where uploaded
     * documents should be saved
     * @return string
     */
    public function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/upload/hotelLogos';
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    public function uploadFile()
    {
        if ($this->getFile() instanceof UploadedFile) { //$this->getFile()->getPath() != $this->getUploadRootDir()
            if($this->getLogo()) {
                $this->setLogo($this->getId(). '_' . uniqid() . '.'.$this->getFile()->getClientOriginalExtension());
            }

            $this->file = $this->getFile()->move($this->getUploadRootDir(), $this->getLogo());
        }
    }

    /**
     * @return bool
     */
    public function deleteFile()
    {
        if ($this->getFile() && is_writable($this->getFile()->getPathname())) {
            $result = unlink($this->getFile()->getPathname());
            if ($result) {
                $this->file = null;
            }

            return $result;
        }

        return false;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getFullTitle(),
            'city' => $this->getCityId() ? $this->getCityId() : null,
        ];
    }

    /**
     * @return mixed
     */
    public function getMyallocatorConfig()
    {
        return $this->myallocatorConfig;
    }

    /**
     * @param mixed $myallocatorConfig
     * @return Hotel
     */
    public function setMyallocatorConfig(MyallocatorConfig $myallocatorConfig)
    {
        $this->myallocatorConfig = $myallocatorConfig;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoomTypesCategories()
    {
        return $this->roomTypesCategories;
    }

    /**
     * @param mixed $roomTypesCategories
     */
    public function setRoomTypesCategories(RoomTypeCategory $roomTypesCategories = null)
    {
        $this->roomTypesCategories = $roomTypesCategories;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getIngredientCategories()
    {
        return $this->ingredientCategories;
    }

    /**
     * @param mixed $ingredientCategories
     */
    public function setIngredientCategories($ingredientCategories)
    {
        $this->ingredientCategories = $ingredientCategories;
    }

    /**
     * @return mixed
     */
    public function getDishMenuCategories()
    {
        return $this->dishMenuCategories;
    }

    /**
     * @param mixed $dishMenuCategories
     */
    public function setDishMenuCategories($dishMenuCategories)
    {
        $this->dishMenuCategories = $dishMenuCategories;
    }

    public function addDishMenuCategories(DishMenuCategory $dishMenuCategory)
    {
        $this->dishMenuCategories->add($dishMenuCategory);
    }

    /**
     * Add Special
     *
     * @param Special $special
     * @return self
     */
    public function addSpecial(Special $special): self
    {
        $this->specials[] = $special;

        return $this;
    }

    /**
     * Remove Special
     *
     * @param Special $special
     * @return self
     */
    public function removeSpecial(Special $special): self
    {
        $this->specials->removeElement($special);

        return $this;
    }

    /**
     * Get Specials
     *
     * @return \Doctrine\Common\Collections\Collection $specials
     */
    public function getSpecials()
    {
        return $this->specials;
    }

    /**
     * @return ContactInfo
     */
    public function getContactInformation(): ?ContactInfo
    {
        return $this->contactInformation;
    }

    /**
     * @param ContactInfo $contactInformation
     * @return Hotel
     */
    public function setContactInformation(ContactInfo $contactInformation): Hotel
    {
        $this->contactInformation = $contactInformation;
        return $this;
    }

    /**
     * @return array
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * @param array $supportedLanguages
     * @return Hotel
     */
    public function setSupportedLanguages(array $supportedLanguages): Hotel
    {
        $this->supportedLanguages = $supportedLanguages;

        return $this;
    }

    public function addSupportedLanguages(string $languageCode) : Hotel
    {
        $this->supportedLanguages[] = $languageCode;

        return $this;
    }

    /**
     * @return array
     */
    public function getAcceptedCardTypes()
    {
        return $this->acceptedCardTypes;
    }

    /**
     * @param array $acceptedCardTypes
     * @return Hotel
     */
    public function setAcceptedCardTypes(array $acceptedCardTypes) : Hotel
    {
        $this->acceptedCardTypes = $acceptedCardTypes;

        return $this;
    }

    /**
     * @param CardType $cardType
     * @return Hotel
     */
    public function addAcceptedCardType(CardType $cardType) : Hotel
    {
        $this->acceptedCardTypes[] = $cardType;

        return $this;
    }

    /**
     * @param CardType $cardType
     * @return Hotel
     */
    public function removeAcceptedCardType(CardType $cardType)
    {
        $this->acceptedCardTypes->remove($cardType);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsInvoiceAccepted(): bool
    {
        return $this->isInvoiceAccepted;
    }

    /**
     * @param bool $isInvoiceAccepted
     * @return Hotel
     */
    public function setIsInvoiceAccepted(bool $isInvoiceAccepted): Hotel
    {
        $this->isInvoiceAccepted = $isInvoiceAccepted;
        return $this;
    }

    /**
     * @return string
     */
    public function getInternationalStreetName(): ?string
    {
        return $this->internationalStreetName;
    }

    /**
     * @param string $internationalStreetName
     * @return Hotel
     */
    public function setInternationalStreetName(string $internationalStreetName = null): Hotel
    {
        $this->internationalStreetName = $internationalStreetName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckinoutPolicy() : ?string
    {
        return $this->checkinoutPolicy;
    }

    /**
     * @param string $checkinoutPolicy
     * @return Hotel
     */
    public function setCheckinoutPolicy(string $checkinoutPolicy = null): Hotel
    {
        $this->checkinoutPolicy = $checkinoutPolicy;

        return $this;
    }

    /**
     * Add image
     * @param Image $image
     * @return Hotel
     */
    public function addImage(Image $image)
    {
        $this->images->add($image);

        return $this;
    }

    /**
     * Remove image
     * @param Image $image
     */
    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return Image|null
     */
    public function getLogoImage(): ?Image
    {
        return $this->logoImage;
    }

    public function setLogoImage(Image $logoImage)
    {
        $this->logoImage = $logoImage;
    }

    public function removeLogoImage()
    {
        $this->logoImage = null;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param mixed $zipCode
     * @return Hotel
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSmokingPolicy(): ?string
    {
        return $this->smokingPolicy;
    }

    /**
     * @param string $smokingPolicy
     * @return Hotel
     */
    public function setSmokingPolicy(string $smokingPolicy = null): Hotel
    {
        $this->smokingPolicy = $smokingPolicy;

        return $this;
    }

    /**
     * @return string
     */
    public function getAboutLink(): ?string
    {
        return $this->aboutLink;
    }

    /**
     * @param string $aboutLink
     * @return Hotel
     */
    public function setAboutLink(string $aboutLink): Hotel
    {
        $this->aboutLink = $aboutLink;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoomsLink(): ?string
    {
        return $this->roomsLink;
    }

    /**
     * @param string $roomsLink
     * @return Hotel
     */
    public function setRoomsLink(string $roomsLink): Hotel
    {
        $this->roomsLink = $roomsLink;

        return $this;
    }

    /**
     * @return string
     */
    public function getMapLink(): ?string
    {
        return $this->mapLink;
    }

    /**
     * @param string $mapLink
     * @return Hotel
     */
    public function setMapLink(string $mapLink): Hotel
    {
        $this->mapLink = $mapLink;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactsLink(): ?string
    {
        return $this->contactsLink;
    }

    /**
     * @param string $contactsLink
     * @return Hotel
     */
    public function setContactsLink(string $contactsLink): Hotel
    {
        $this->contactsLink = $contactsLink;

        return $this;
    }

    /**
     * @return string
     */
    public function getPollLink(): ?string
    {
        return $this->pollLink;
    }

    /**
     * @param string $pollLink
     * @return Hotel
     */
    public function setPollLink(string $pollLink): Hotel
    {
        $this->pollLink = $pollLink;

        return $this;
    }
    /**
     * @return int
     */
    public function getPackageArrivalTime(): ?int
    {
        return $this->packageArrivalTime;
    }

    /**
     * @param int $packageArrivalTime
     * @return Hotel
     */
    public function setPackageArrivalTime(?int $packageArrivalTime): Hotel
    {
        $this->packageArrivalTime = $packageArrivalTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getPackageDepartureTime(): ?int
    {
        return $this->packageDepartureTime;
    }

    /**
     * @param int $packageDepartureTime
     * @return Hotel
     */
    public function setPackageDepartureTime(?int $packageDepartureTime): Hotel
    {
        $this->packageDepartureTime = $packageDepartureTime;

        return $this;
    }

    /**
     * @param UploaderHelper $helper
     * @param CacheManager $cacheManager
     * @return array
     */
    public function getImagesData(UploaderHelper $helper, CacheManager $cacheManager)
    {
        $imagesData = [];
        /** @var Image $image */
        foreach ($this->getImages() as $image) {
            $imageData = ['isMain' => $image->getIsDefault()];
            $imageData['url'] = $cacheManager->getBrowserPath($helper->asset($image, 'imageFile'), 'scaler');
            if ($image->getWidth()) {
                $imageData['width'] = (int)$image->getWidth();
            }
            if ($image->getHeight()) {
                $imageData['height'] = (int)$image->getHeight();
            }
            $imagesData[] = $imageData;
        }

        return $imagesData;
    }

    /**
     * @param bool $isFull
     * @param UploaderHelper $uploaderHelper
     * @param CacheManager|null $cacheManager
     * @return array
     */
    public function getJsonSerialized($isFull = false, UploaderHelper $uploaderHelper= null, CacheManager $cacheManager = null)
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getName(),
        ];

        if ($isFull) {
            $comprehensiveData = [
                'isEnabled' => $this->getIsEnabled(),
                'isDefault' => $this->getIsDefault(),
                'isHostel' => $this->getIsHostel(),
                'description' => $this->getDescription(),
                'facilities' => $this->getFacilities()
            ];
            if (!is_null($uploaderHelper) && !is_null($cacheManager)) {
                $comprehensiveData['photos'] = $this->getImagesData($uploaderHelper, $cacheManager);
                if (!empty($this->getLogoImage())) {
                    $comprehensiveData['logoUrl'] = $cacheManager->getBrowserPath($uploaderHelper->asset($this->getLogoImage(), 'imageFile'), 'scaler');
                }
            } else {
                throw new \InvalidArgumentException('It\'s required uploader helper and current domain for serialization of the full information about the hotel!');
            }

            if (!is_null($this->latitude)) {
                $comprehensiveData['latitude'] = $this->latitude;
            }
            if (!is_null($this->longitude)) {
                $comprehensiveData['longitude'] = $this->longitude;
            }
            if (!empty($this->street)) {
                $comprehensiveData['street'] = $this->street;
            }
            if (!empty($this->house)) {
                $comprehensiveData['house'] = $this->house;
            }
            if (!empty($this->corpus)) {
                $comprehensiveData['corpus'] = $this->corpus;
            }
            if (!empty($this->flat)) {
                $comprehensiveData['flat'] = $this->flat;
            }
            if (!empty($this->zipCode)) {
                $comprehensiveData['zipCode'] = $this->zipCode;
            }
            if (!empty($this->getContactInformation())) {
                $contactsInfo = $this->getContactInformation();
                $contactsInfoArray = [];
                if (!empty($contactsInfo->getEmail())) {
                    $contactsInfoArray['email'] = $contactsInfo->getEmail();
                }
                if (!empty($contactsInfo->getPhoneNumber())) {
                    $contactsInfoArray['phone'] = $contactsInfo->getPhoneNumber();
                }
                if (!empty($contactsInfoArray)) {
                    $comprehensiveData['contacts'] = $contactsInfoArray;
                }
            }

            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}
