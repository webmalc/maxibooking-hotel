<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

abstract class AbstractResultGenerator implements OnlineResultsGeneratorInterface
{
    protected const TYPE = '';
    /** @var SearchFactory $search */
    protected $search;
    /** @var SearchQuery */
    protected $searchQuery;
    /** @var  callable */
    protected $searchConfigurator;
    /** @var  ArrayCollection */
    protected $results;

    /**
     * AbstractResultGenerator constructor.
     * @param SearchFactory $search
     */
    public function __construct(SearchFactory $search)
    {
        $this->search = $search;
        $this->searchQuery = new SearchQuery();
        $this->results = new ArrayCollection();
    }

    protected function resultOnlineInstanceCreator($searchResult): OnlineResultInstance
    {
        if ($searchResult instanceof SearchResult) {
            $instance = $this->createOnlineResultInstance($searchResult->getRoomType(), [$searchResult]);
        } elseif (is_array($searchResult)) {
            $roomType = $searchResult['roomType'];
            $results = $searchResult['results'];
            $instance = $this->createOnlineResultInstance($roomType, $results);
        } else {
            throw new OnlineBookingSearchException('Cannot create OnlineResult from searchResult');
        }

        return $instance;
    }

    public function getResults(OnlineSearchFormData $formData): ArrayCollection
    {
        $this->initSearchQuery($formData);
        $this->configureSearch();
        $searchResults = $this->search($this->searchQuery);
        if (!empty($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $this->results->add($this->resultOnlineInstanceCreator($searchResult));
            }
        }
        $this->resultsHandle();

        return $this->results;
    }

    protected function search(SearchQuery $searchQuery){

        return $this->search->search($searchQuery);
    }

    private function initSearchQuery(OnlineSearchFormData $data): void
    {
        $roomType = $data->getRoomType();
        if ($roomType) {
            /** @var RoomType $roomType */
            $this->searchQuery->addRoomType($roomType->getId());
        } elseif ($data->getHotel()) {
            $this->searchQuery->addRoomTypeArray($data->getActualRoomTypeIds());
        }
        $this->searchQuery->begin = $data->getBegin();
        $this->searchQuery->end = $data->getEnd();
        $this->searchQuery->adults = (int)$data->getAdults();
        $this->searchQuery->children = (int)$data->getChildren();
        $this->searchQuery->isOnline = true;
        $this->searchQuery->accommodations = true;
        $this->searchQuery->forceRoomTypes = false;
        if ($data->getChildrenAge()) {
            $this->searchQuery->setChildrenAges($data->getChildrenAge());
        };

    }

    protected function configureSearch()
    {
        $configurator = $this->searchConfigurator;
        if (is_callable($configurator)) {
            $configurator($this->searchQuery, $this->search);
        }
    }

    public function setSearchConfigurator(callable $configurator)
    {
        $this->searchConfigurator = $configurator;
    }


    protected function resultsHandle(): void
    {
        $this->separateByAdditionalDays();
        $this->filterByCapacity();
        $this->injectSearchQuery();
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

    /**
     * @param array $searchResults
     * @return array
     */
    private function filterByCapacity()
    {
        $result = [];
        $groups = $this->groupedByRoomTypeCategory($this->results);
        foreach ($groups as $group) {
            usort(
                $group,
                function ($a, $b) {
                    return $a->getRoomType()->getTotalPlace() <=> $b->getRoomType()->getTotalPlace();
                }
            );
            $result[] = $group[0];
        }

        $this->results = new ArrayCollection($result);
    }

    private function injectSearchQuery()
    {
        foreach ($this->results as $result) {
            $result->setQuery($this->searchQuery);
        }
    }

    /**
     * @param array $searchResults
     * @return array
     */
    private function addLeftRoomKeys(array $searchResults)
    {
        foreach ($searchResults as $key => $searchResult) {
            $roomTypeCategoryId = $searchResult['roomType']->getId();
            $begin = $searchResult['query']->begin;
            $end = $searchResult['query']->end;
            $leftRoomKey = $roomTypeCategoryId.$begin->format('dmY').$end->format('dmY');
            $searchResults[$key]['leftRoomKey'] = $leftRoomKey;
        }

        return $searchResults;
    }

    /**
     * Divide results to match and additional dates
     */
    private function separateByAdditionalDays(): void
    {

        $result = [];
        foreach ($this->results as $resultInstance) {
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
                $instance = $this->createOnlineResultInstance($resultInstance->getRoomType(), array_values($group));
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

        $this->results = new ArrayCollection($result);
    }

    public function getType(): string
    {
        if (empty(static::TYPE)) {
            throw new OnlineBookingSearchException('Generator MUST have type');
        }

        return static::TYPE;
    }

    protected function addResult(OnlineResultInstance $resultInstance)
    {
        $this->results->attach($resultInstance);
    }

    protected function removeResult(OnlineResultInstance $resultInstance)
    {
        $this->results->detach($resultInstance);
    }

    protected function createOnlineResultInstance($roomType, $results): OnlineResultInstance
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
