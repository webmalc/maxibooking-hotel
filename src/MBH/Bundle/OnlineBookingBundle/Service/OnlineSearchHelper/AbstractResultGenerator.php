<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Special;

abstract class AbstractResultGenerator implements OnlineResultsGeneratorInterface
{
    protected const TYPE = '';
    /** @var SearchFactory $search */
    protected $search;
    /** @var array  */
    protected $options;
    /** @var Helper  */
    protected $helper;
    /** @var  array */
    protected $cache;

    /**
     * AbstractResultGenerator constructor.
     * @param SearchFactory $search
     * @param array $options
     * @param Helper $helper
     * @param array $cache
     */
    public function __construct(SearchFactory $search, array $options, Helper $helper, array $cache)
    {
        $this->cache = $cache['is_enabled'];
        $this->search = $search;
        $this->options = $options;
        $this->helper = $helper;
    }

    public function getResults(OnlineSearchFormData $formData): ArrayCollection
    {
        $results = new ArrayCollection();
        $searchQuery = $this->initSearchQuery($formData);
        $searchResults = $this->search($searchQuery, $formData->getRoomType(), $formData->getSpecial());
        if (!empty($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $results->add($this->resultOnlineInstanceCreator($searchResult, $searchQuery));
            }
        }
        $results = $this->resultsHandle($searchQuery, $results);

        return $results;
    }

    protected function resultOnlineInstanceCreator($searchResult, SearchQuery $searchQuery): OnlineResultInstance
    {
        if ($searchResult instanceof SearchResult) {
            $instance = $this->createOnlineResultInstance($searchResult->getRoomType(), [$searchResult], $searchQuery);
        } elseif (is_array($searchResult)) {
            $roomType = $searchResult['roomType'];
            $results = $searchResult['results'];
            $instance = $this->createOnlineResultInstance($roomType, $results, $searchQuery);
        } else {
            throw new OnlineBookingSearchException('Cannot create OnlineResult from searchResult');
        }

        return $instance;
    }

    protected function search(SearchQuery $searchQuery, $roomType = null, Special $special = null)
    {
        return $this->search->search($searchQuery);
    }

    private function initSearchQuery(OnlineSearchFormData $data): SearchQuery
    {
        $searchQuery = new SearchQuery();
        $roomType = $data->getRoomType();
        if ($roomType) {
            /** @var RoomType $roomType */
            $searchQuery->addRoomType($roomType->getId());
        } elseif ($data->getHotel()) {
            $searchQuery->addRoomTypeArray($data->getActualRoomTypeIds());
        }
        $searchQuery->begin = $data->getBegin();
        $searchQuery->end = $data->getEnd();
        $searchQuery->adults = (int)$data->getAdults();
        $searchQuery->children = (int)$data->getChildren();
        $searchQuery->isOnline = true;
        $searchQuery->accommodations = true;
        $searchQuery->forceRoomTypes = false;
        $searchQuery->memcached = $this->cache;
        if ($data->getChildrenAge()) {
            $searchQuery->setChildrenAges($data->getChildrenAge());
        };

        return $searchQuery;
    }

    protected function resultsHandle(SearchQuery $searchQuery, ArrayCollection $results): ArrayCollection
    {
        return $this->injectSearchQuery($searchQuery, $results);
    }

    //Исходя из старого кода предполагалось что могут быть возвращены результаты для одной группы - несколько типов комнат.
    //Пока отключено до выяснения.
    protected function filterByCapacity(ArrayCollection $results): ArrayCollection
    {
        $result = [];
        $groups = $this->groupedByRoomTypeCategory($results);
        foreach ($groups as $group) {
            usort(
                $group,
                function ($a, $b) {
                    return $a->getRoomType()->getTotalPlace() <=> $b->getRoomType()->getTotalPlace();
                }
            );
            $result[] = $group[0];
        }

        return new ArrayCollection($result);

    }
    private function groupedByRoomTypeCategory(ArrayCollection $onlineInstances): array
    {
        $groups = [];
        foreach ($onlineInstances as $instance) {
            /** @var OnlineResultInstance $instance */
            $roomType = $instance->getRoomType();
            if ($roomType instanceof RoomTypeCategory) {
                $categoryId = $roomType->getId();
            } elseif ($roomType instanceof RoomType) {
                $categoryId = $roomType->getCategory()->getId();
            }

            if (isset($categoryId)) {
                $groups[$categoryId][] = $instance;
            }
        }

        return $groups;
    }



    private function injectSearchQuery(SearchQuery $searchQuery, ArrayCollection $results): ArrayCollection
    {
        foreach ($results as $result) {
            $result->setQuery($searchQuery);
        }

        return $results;
    }

    /**
     * Divide results to match and additional dates
     */
    protected function separateByAdditionalDays(SearchQuery $searchQuery, ArrayCollection $results): ArrayCollection
    {

        $result = [];
        foreach ($results as $resultInstance) {
            /** @var OnlineResultInstance $resultInstance */
            $groups = [];

            foreach ($resultInstance->getResults() as $keyNeedleInstance => $searchNeedleInstance) {
                /** @var SearchResult $searchNeedleInstance */
                $needle = $searchNeedleInstance->getBegin()->format('dmY').$searchNeedleInstance->getEnd()->format(
                        'dmY'
                    );
                foreach ($resultInstance->getResults() as $searchKey => $searchInstance) {
                    /** @var SearchResult $searchInstance */
                    $hayStack = $searchInstance->getBegin()->format('dmY').$searchInstance->getEnd()->format('dmY');
                    if ($needle == $hayStack) {
                        $groups[$needle][$searchKey] = $searchInstance;
                    }
                }
            }
            foreach ($groups as $group) {
                $instance = $this->createOnlineResultInstance($resultInstance->getRoomType(), array_values($group), $searchQuery);
                $result[] = $instance;
            }
        }

        usort(
            $result,
            function ($resA, $resB) {
                $priceA = $resA->getResults()->first()->getPrices();
                $priceB = $resB->getResults()->first()->getPrices();

                return reset($priceA) <=> reset($priceB);
            }
        );

        return new ArrayCollection($result);
    }

    public function getType(): string
    {
        if (empty(static::TYPE)) {
            throw new OnlineBookingSearchException('Generator MUST have type');
        }

        return static::TYPE;
    }

    protected function createOnlineResultInstance($roomType, $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        $instance = new OnlineResultInstance();
        $instance->setType(static::TYPE);
        if ($roomType instanceof RoomType || $roomType instanceof RoomTypeCategory) {
            $instance->setRoomType($roomType);
        }
        foreach ($results as $searchResult) {
            $instance->addResult($searchResult);
        }

        return $instance;
    }

}
