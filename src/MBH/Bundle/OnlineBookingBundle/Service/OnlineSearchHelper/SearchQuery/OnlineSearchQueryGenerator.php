<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\SearchQuery;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

class OnlineSearchQueryGenerator
{
    /** @var bool */
    private $isCache;


    public function __construct(array $cacheOptions)
    {
        $this->isCache = $cacheOptions['is_enabled'];
    }

    public function createSearchQuery(OnlineSearchFormData $searchFormData): SearchQuery
    {
        $searchQuery = new SearchQuery();
        $roomType = $searchFormData->getRoomType();
        if ($roomType) {
            /** @var RoomType $roomType */
            $searchQuery->addRoomType($roomType->getId());
        } elseif ($searchFormData->getHotel()) {
            $searchQuery->addRoomTypeArray($searchFormData->getActualRoomTypeIds());
        }
        $searchQuery->begin = $searchFormData->getBegin();
        $searchQuery->end = $searchFormData->getEnd();
        $searchQuery->adults = (int)$searchFormData->getAdults();
        $searchQuery->children = (int)$searchFormData->getChildren();
        $searchQuery->isOnline = true;
        $searchQuery->accommodations = true;
        $searchQuery->forceRoomTypes = false;
        $searchQuery->memcached = $this->isCache && $searchFormData->isCache();
        if ($searchFormData->getChildrenAge()) {
            $searchQuery->setChildrenAges($searchFormData->getChildrenAge());
        };
        if ($searchFormData->getSpecial()) {
            $searchQuery->setSpecial($searchFormData->getSpecial());
        }

        return $searchQuery;
    }


}