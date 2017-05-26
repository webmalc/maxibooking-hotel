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
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    /** @var  OnlineSearchFormData */
    protected $originalFormData;
    /** @var ContainerInterface */
    protected $container;

    /**
     * AbstractResultGenerator constructor.
     * @param SearchFactory $search
     * @param array $options
     * @param Helper $helper
     * @param array $cache
     * @param ContainerInterface $container
     */
    public function __construct(SearchFactory $search, array $options, Helper $helper, array $cache, ContainerInterface $container)
    {
        $this->cache = $cache['is_enabled'];
        $this->search = $search;
        $this->options = $options;
        $this->helper = $helper;
        $this->container = $container;
    }

    public function getResults(OnlineSearchFormData $formData): ArrayCollection
    {
        $this->originalFormData = $formData;
        $results = $this->searchByFormData($formData);
        $results = $this->resultsHandle($results);

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

    protected function createOnlineResultInstance($roomType, array $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        /** @var OnlineResultInstance $instance */
        $instance = $this->container->get('mbh.online_result_instance');
        $instance->setType(static::TYPE);
        if ($roomType instanceof RoomType || $roomType instanceof RoomTypeCategory) {
            $instance->setRoomType($roomType);
        }
        foreach ($results as $searchResult) {
            $instance->addResult($searchResult);
        }
        $instance->setQuery($searchQuery);

        return $instance;
    }


    abstract protected function searchByFormData(OnlineSearchFormData $formData): ArrayCollection;

    protected function search(SearchQuery $searchQuery)
    {
        return $this->search->search($searchQuery);
    }

    protected function initSearchQuery(OnlineSearchFormData $data): SearchQuery
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
        $searchQuery->memcached = $this->cache && $data->isCache();
        if ($data->getChildrenAge()) {
            $searchQuery->setChildrenAges($data->getChildrenAge());
        };
        if ($data->getSpecial()) {
            $searchQuery->setSpecial($data->getSpecial());
        }

        return $searchQuery;
    }

    protected function resultsHandle(ArrayCollection $results): ArrayCollection
    {
        return $results;
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

    public function getType(): string
    {
        if (empty(static::TYPE)) {
            throw new OnlineBookingSearchException('Generator MUST have type');
        }

        return static::TYPE;
    }



}
