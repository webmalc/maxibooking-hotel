<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Special;

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
    /** @var  string */
    protected $type;
    /** @var Special */
    protected $special;

    /**
     * OnlineResultInstance constructor.
     */
    public function __construct()
    {
        $this->results = new ArrayCollection();
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
    public function getMainImage(): ?RoomTypeImage
    {
        return $this->getImages()['mainimage'];
    }

    /**
     * @return string
     */
    public function getLeftRoomKey(): string
    {
        return $this->roomType->getId().
            $this->getFirstResult()->getTariff()->getId().
            $this->getQuery()->begin->format('dmY').
            $this->getQuery()->end->format('dmY');
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
            $mainImage = $this->roomType->getMainImage();
            $roomTypes = $this->roomType->getTypes();
            foreach ($roomTypes as $roomType) {
                $images = array_merge($roomType->getImages()->toArray());
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

    public function isCategory()
    {
        return ($this->getRoomType() instanceof RoomTypeCategory);
    }

    public function getFirstResult()
    {
        return $this->getResults()->first();
    }

    public function getRemain()
    {
        return $this->getFirstResult()->getRoomsCount();
    }

    /**
     * @return Special
     */
    public function getSpecial(): Special
    {
        return $this->special;
    }

    /**
     * @param Special $special
     */
    public function setSpecial(Special $special)
    {
        $this->special = $special;
    }

    public function isSameVirtualRoomInSpec()
    {
        $result = false;
        if (!empty($this->results) && !empty($this->special)) {
            $specialVirtualRoom = $this->special->getVirtualRoom();
            /** @var SearchResult $result */
            $searchResult = $this->results->first();
            $virtualRoom = $searchResult->getVirtualRoom();
            if ($specialVirtualRoom && $virtualRoom && $specialVirtualRoom->getId() === $virtualRoom->getId()) {
                $result = true;
            }
        }

        return $result;
    }



}