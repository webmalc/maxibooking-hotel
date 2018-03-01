<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters\OnlineSorterInterface;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

class OnlineDataProviderWrapper implements OnlineDataProviderWrapperInterface
{

    /** @var OnlineDataProviderInterface */
    private $dataProvider;

    /** @var OnlineSorterInterface */
    private $onlineSorter;

    /** @var bool */
    private $isCache;

    /**
     * OnlineDataProviderContainer constructor.
     * @param OnlineDataProviderInterface $dataProvider
     * @param OnlineSorterInterface $onlineSorter
     */
    public function __construct(OnlineDataProviderInterface $dataProvider, OnlineSorterInterface $onlineSorter, array $cacheOptions)
    {
        $this->dataProvider = $dataProvider;
        $this->onlineSorter = $onlineSorter;
        $this->isCache = $cacheOptions['is_enabled'];
    }

    public function getResults(OnlineSearchFormData $formData): array
    {
        $searchQuery = clone $this->createSearchQuery(clone $formData);
        $result = $this->dataProvider->search($formData, $searchQuery);
        $result = $this->onlineSorter->sort($result);

        return $result;
    }

    public function getType(): string
    {
        return $this->dataProvider->getType();
    }

    private function createSearchQuery(OnlineSearchFormData $searchFormData): SearchQuery
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