<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

class OnlineResultInstance
{
    /** @var  RoomType */
    protected $roomType;
    /** @var  ArrayCollection */
    protected $results;
    /** @var  bool */
    protected $additional;
    /** @var  SearchQuery */
    protected $query;
    /** @var  array */
    protected $forceRoomType;
    /** @var  RoomTypeImage */
    protected $mainImage;
    /** @var  string */
    protected $leftRoomKey;
    /** @var  string */
    protected $type;

    /**
     * OnlineResultInstance constructor.
     */
    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->images = new ArrayCollection();
    }


    /**
     * @return RoomType|RoomTypeCategory
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * @param RoomType|RoomTypeCategory $roomType
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults(): ArrayCollection
    {
        return $this->results;
    }

    /**
     * @param SearchResult $result
     */
    public function addResult(SearchResult $result)
    {
        $this->results->add($result);
    }

    /**
     * @return bool
     */
    public function isAdditional(): bool
    {
        return $this->additional;
    }

    /**
     * @param bool $additional
     */
    public function setAdditional(bool $additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }

    /**
     * @param SearchQuery $query
     */
    public function setQuery(SearchQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getForceRoomType(): array
    {
        return $this->forceRoomType;
    }

    /**
     * @param array $forceRoomType
     */
    public function setForceRoomType(array $forceRoomType)
    {
        $this->forceRoomType = $forceRoomType;
    }

    /**
     * @return RoomTypeImage
     */
    public function getMainImage(): RoomTypeImage
    {
        return $this->mainImage;
    }

    /**
     * @param RoomTypeImage $mainImage
     * @return $this
     */
    public function setMainImage(RoomTypeImage $mainImage)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getLeftRoomKey(): string
    {
        return $this->leftRoomKey;
    }

    /**
     * @param string $leftRoomKey
     */
    public function setLeftRoomKey(string $leftRoomKey)
    {
        $this->leftRoomKey = $leftRoomKey;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public function getImages()
    {
        $images = [];
        $mainImage = null;

        if ($this->roomType instanceof RoomTypeCategory) {
            /** @var RoomTypeCategory $roomTypeCategory */
            $roomTypes = $roomTypeCategory->getTypes();
            foreach ($roomTypes as $roomType) {
                $mainImage = $roomType->getMainImage();
                $images = $roomType->getImages()->toArray();
            }
        } elseif ($this->roomType instanceof RoomType) {
            $mainImage = $this->roomType->getMainImage();
            $images = $this->roomType->getImages()->toArray();
        }

        return [
            'images' => $images,
            'mainimage' => $mainImage,
        ];
    }

    public function getDates()
    {
        $firstResult = $this->results->first();

        return [
            'begin' => $firstResult->getBegin(),
            'end' => $firstResult->getEnd(),
        ];
    }

}