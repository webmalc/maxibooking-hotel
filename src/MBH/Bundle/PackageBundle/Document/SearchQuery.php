<?php

namespace MBH\Bundle\PackageBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use Symfony\Component\Validator\Constraints as Assert;

/**
  * Class SearchQuery
 * @package MBH\Bundle\PackageBundle\Lib
 * @ODM\Document(collection="SearchQuery", repositoryClass="MBH\Bundle\PackageBundle\Document\SearchQueryRepository")
 * @Gedmo\Loggable()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class SearchQuery extends Base
{

    use TimestampableDocument;

    use SoftDeleteableDocument;

    use BlameableDocument;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    public $memcached = true;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="form.searchType.check_in_date_not_filled")
     * @Assert\Date()
     */
    public $begin;
    
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull(message="orm.searchType.check_out_date_not_filled")
     * @Assert\Date()
     */
    public $end;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    public $excludeBegin = null;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    public $excludeEnd = null;
    
    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\NotNull(message="form.searchType.adults_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 12,
     *     minMessage = "form.searchType.adults_amount_less_zero"
     * )
     */
    public $adults;
    
    /**
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\NotNull(message="orm.searchType.children_amount_not_filled")
     * @Assert\Range(
     *     min = 0,
     *     max = 6,
     *     minMessage = "form.searchType.children_amount_less_zero"
     * )
     */
    public $children;

    /**
     * @var array
     * @ODM\Field(type="collection")
     */
    public $childrenAges = [];

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     */
    public $isOnline = false;
    
    /**
     * RoomTypes ids
     * @ODM\Field(type="collection")
     * @var []
     */
    public $roomTypes = [];

    /**
     * @var boolean
     * @ODM\Field(type="boolean")
     */
    public $forceRoomTypes = false;

    /**
     * ExcludeRoomTypes ids
     * @ODM\Field(type="collection")
     * @var []
     */
    public $excludeRoomTypes = [];

    /**
     * With accommodations on/off
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $accommodations = false;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $grouped = false;

    /**
     * @var string
     */
    public $room;

    /**
     * @var Promotion
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Promotion")
     */
    protected $promotion;

    /**
     * @var Special
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Special")
     */
    protected $special;
    
    /**
     * Tariff id
     * @var mixed
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Tariff")
     */
    public $tariff;

    /**
     * @var Package
     */
    protected $excludePackage;

    /**
     * Additional days for search
     * @var int
     * @ODM\Field(type="integer")
     * @Assert\Range(
     *     min = 0,
     *     max = 10,
     *     maxMessage = "form.searchType.range_validator",
     *     minMessage = "form.searchType.range_validator"
     * )
     */
    public $range = 0;

    /**
     * @var RoomType[]|array ids
     * @ODM\Field(type="collection")
     */
    public $availableRoomTypes = [];

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $forceBooking = false;

    /**
     * @var int
     * @ODM\Field(type="integer")
     */
    public $infants = 0;

    /**
     * @var int
     * @Assert\Range(min = 0)
     * @ODM\Field(type="integer")
     */
    public $limit;

    /** @var bool  */
    protected $save = false;

    /** @var  string */
    protected $querySavedId;
    /**
     * @param $id
     */
    public function addExcludeRoomType($id)
    {
        if (!in_array($id, $this->excludeRoomTypes) && !empty($id)) {
            $this->excludeRoomTypes[] = $id;
        }
    }

    /**
     * @param $id
     */
    public function addAvailableRoomType($id)
    {
        if (!in_array($id, $this->availableRoomTypes) && !empty($id)) {
            $this->availableRoomTypes[] = $id;
        }
    }

    /**
     * @param Hotel $hotel
     * @return $this
     */
    public function addHotel(Hotel $hotel = null)
    {
        if (empty($hotel)) {
            return $this;
        }
        $roomTypes = $hotel->getRoomTypes();
        foreach ($roomTypes as $roomType) {
            $this->addRoomType($roomType->getId());
        }

        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    public function addRoomType($id)
    {
        if (!empty($this->availableRoomTypes) && !in_array($id, $this->availableRoomTypes)) {
            return false;
        }

        if (!in_array($id, $this->roomTypes) && !empty($id)) {
            $this->roomTypes[] = $id;
        }
    }

    /**
     * @param array $ages
     */
    public function setChildrenAges(array $ages)
    {
        foreach ($ages as $age) {
            if (is_numeric($age)) {
                $this->childrenAges[] = (int) $age;
            }
        }
    }

    /**
     * @return int
     */
    public function getTotalPlaces()
    {
        return (int) $this->adults + (int) $this->children;
    }

    /**
     * @return Promotion
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @param mixed $promotion
     */
    public function setPromotion($promotion = false)
    {
        if (!$promotion instanceof Promotion && $promotion !== false) {
            $promotion = null;
        }

        $this->promotion = $promotion;
    }

    /**
     * @return Special
     */
    public function getSpecial(): ?Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     * @return SearchQuery
     */
    public function setSpecial(Special $special = null): SearchQuery
    {
        $this->special = $special;
        return $this;
    }

    /**
     * @return Package
     */
    public function getExcludePackage(): ?Package
    {
        return $this->excludePackage;
    }

    /**
     * @param Package $excludePackage
     * @return SearchQuery
     */
    public function setExcludePackage(Package $excludePackage = null): SearchQuery
    {
        $this->excludePackage = $excludePackage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSave(): bool
    {
        return $this->save;
    }

    /**
     * Save SearchQuery in DB
     * @param bool $save
     * @return SearchQuery
     */
    public function setSave(bool $save): SearchQuery
    {
        $this->save = $save;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuerySavedId(): ?string
    {
        return $this->querySavedId;
    }

    /**
     * @param string $querySavedId
     * @return SearchQuery
     */
    public function setQuerySavedId(string $querySavedId): SearchQuery
    {
        $this->querySavedId = $querySavedId;

        return $this;
    }


}
