<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate;

/**
 * @ODM\Document(collection="Hotels")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields="fullTitle", message="Такой отель уже существует")
 * @ODM\HasLifecycleCallbacks
 */
class Hotel extends Base
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
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.max_name"
     * )
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.min_name"
     * )
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="prefix")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_prefix",
     *      max=100,
     *      maxMessage="validator.document.hotel.max_prefix"
     * )
     */
    protected $prefix;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isHostel = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isDefault = false;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric", message= "validator.document.hotel.wrong_latitude")
     */
    protected $latitude;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric", message="validator.document.hotel.wrong_longitude")
     */
    protected $longitude;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection()
     */
    protected $type = [];

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection()
     */
    protected $theme = [];

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection()
     */
    protected $facilities = [];

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Int()
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

    /** @ODM\ReferenceMany(targetDocument="Room", mappedBy="hotel") */
    protected $rooms;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff", mappedBy="hotel") */
    protected $tariffs;

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

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Country")
     */
    protected $country;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Region")
     */
    protected $region;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="City")
     */
    protected $city;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $street;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $house;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $corpus;

    /**
     * @Gedmo\Versioned
     * @ODM\String
     */
    protected $flat;

    /**
     * @var Housing[]
     * @ODM\ReferenceMany(targetDocument="Housing", mappedBy="hotel")
     */
    protected $housings;


    /**
     * @var UploadedFile
     * @Assert\File(maxSize="6M", mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     * }, mimeTypesMessage="validator.document.OrderDocument.file_type")
     */
    protected $file;

    /**
     * @ODM\String
     * @var string
     */
    protected $logo;

    /**
     * @var Organization|null
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization", mappedBy="hotels")
     */
    protected $organization;

    /**
     * @var int
     * @ODM\Int
     * @Assert\Type(type="numeric")
     */
    protected $vegaAddressId;

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
     * Get prefix
     *
     * @return string $prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
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
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function __construct()
    {
        $this->roomTypes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tariffs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return \Doctrine\Common\Collections\Collection $roomTypes
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
     * Get vashotelConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\VashotelConfig $vashotelConfig
     */
    public function getVashotelConfig()
    {
        return $this->vashotelConfig;
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
     * Get oktogoConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig $oktogoConfig
     */
    public function getOktogoConfig()
    {
        return $this->oktogoConfig;
    }

    /**
     * Add servicesCategory
     *
     * @param \MBH\Bundle\PriceBundle\Document\ServiceCategory $servicesCategory
     */
    public function addServicesCategory(\MBH\Bundle\PriceBundle\Document\ServiceCategory $servicesCategory)
    {
        $this->servicesCategories[] = $servicesCategory;
    }

    /**
     * Remove servicesCategory
     *
     * @param \MBH\Bundle\PriceBundle\Document\ServiceCategory $servicesCategory
     */
    public function removeServicesCategory(\MBH\Bundle\PriceBundle\Document\ServiceCategory $servicesCategory)
    {
        $this->servicesCategories->removeElement($servicesCategory);
    }

    /**
     * Get servicesCategories
     *
     * @return \Doctrine\Common\Collections\Collection $servicesCategories
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
            foreach ($serviceCategory->getServices() as $service) {
                if ($online && !$service->getIsOnline()) {
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
     * Get isHostel
     *
     * @return boolean $isHostel
     */
    public function getIsHostel()
    {
        return $this->isHostel;
    }

    /**
     * Set country
     *
     * @param \MBH\Bundle\HotelBundle\Document\Country $country
     * @return self
     */
    public function setCountry(\MBH\Bundle\HotelBundle\Document\Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \MBH\Bundle\HotelBundle\Document\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set region
     *
     * @param \MBH\Bundle\HotelBundle\Document\Region $region
     * @return self
     */
    public function setRegion(\MBH\Bundle\HotelBundle\Document\Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \MBH\Bundle\HotelBundle\Document\Region $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city
     *
     * @param \MBH\Bundle\HotelBundle\Document\City $city
     * @return self
     */
    public function setCity(\MBH\Bundle\HotelBundle\Document\City $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \MBH\Bundle\HotelBundle\Document\City $city
     */
    public function getCity()
    {
        return $this->city;
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
     * Get latitude
     *
     * @return float $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
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
     * Get longitude
     *
     * @return float $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
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
     * Get type
     *
     * @return collection $type
     */
    public function getType()
    {
        return $this->type;
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
     * Get theme
     *
     * @return collection $theme
     */
    public function getTheme()
    {
        return $this->theme;
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
     * Get facilities
     *
     * @return collection $facilities
     */
    public function getFacilities()
    {
        return $this->facilities;
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
     * Get rating
     *
     * @return int $rating
     */
    public function getRating()
    {
        return $this->rating;
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
     * Get bookingConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\BookingConfig $bookingConfig
     */
    public function getBookingConfig()
    {
        return $this->bookingConfig;
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
     * Get hotelinnConfig
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Document\HotelinnConfig $hotelinnConfig
     */
    public function getHotelinnConfig()
    {
        return $this->hotelinnConfig;
    }

    /**
     * @PreUpdate
     */
    public function preUpdate()
    {
        $this->fillLocationByCity();
    }

    private function fillLocationByCity()
    {
        if ($this->getCity()) {
            $this->setCountry($this->getCity()->getCountry());
            $this->setRegion($this->getCity()->getRegion());
        }
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
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
     */
    public function setHouse($house)
    {
        $this->house = $house;
    }

    /**
     * @return mixed
     */
    public function getCorpus()
    {
        return $this->corpus;
    }

    /**
     * @param mixed $corpus
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
     * @return Housing[]
     */
    public function getHousings()
    {
        return $this->housings;
    }

    public function addHousing(Housing $housing)
    {
        $this->housings[] = $housing;
    }

    /**
     * @param Housing[] $$housings
     */
    public function setHousings(array $housings)
    {
        $this->housings = $housings;
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
    public function getPath()
    {
        return $this->getUploadRootDir().DIRECTORY_SEPARATOR.$this->getLogo();
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile()
    {
        if (!$this->file && $this->logo && is_file($this->getPath())) {
            $this->file = new UploadedFile($this->getPath(), $this->getLogo());
        }

        return $this->file;
    }

    public function getLogoUrl()
    {
        if($this->getFile()) {
            return '/upload/hotelLogos/'.$this->getFile()->getClientOriginalName();
        }
        return null;
    }

    public function uploadFile()
    {
        if (null === $this->getFile()) {
            return;
        }

        if($this->getLogo()) {
            $this->setLogo($this->getId().'.'.$this->getFile()->getClientOriginalExtension());
        }

        $this->getFile()->move($this->getUploadRootDir(), $this->getLogo());
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

    /**
     * @return int
     */
    public function getVegaAddressId()
    {
        return $this->vegaAddressId;
    }

    /**
     * @param int $vegaAddressId
     */
    public function setVegaAddressId($vegaAddressId)
    {
        $this->vegaAddressId = $vegaAddressId;
    }
}
