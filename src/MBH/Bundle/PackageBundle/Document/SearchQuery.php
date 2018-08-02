<?php

namespace MBH\Bundle\PackageBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
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
    use SearchQueryTrait;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     */
    public $memcached = true;
    /**
     * With accommodations on/off
     * @var bool
     * @ODM\Field(type="boolean")
     */
    public $accommodations = false;

    /**
     * @var Package
     */
    protected $excludePackage;

    /** @var bool  */
    protected $save = false;

    /** @var  string */
    protected $querySavedId;

    /**
     * @var int
     * @Assert\Range(min = 0)
     * @ODM\Field(type="integer")
     */
    public $limit;

    /**
     * @var string
     */
    public $room;

    /**
     * RoomTypes ids
     * @ODM\Field(type="collection")
     * @var []
     */
    public $roomTypes = [];

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

        return true;
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
}
