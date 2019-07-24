<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Form\RoomType;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters\OnlineCreatorInterface;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

/**
 * Class OnlineResultCreator
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class OnlineResultCreator implements OnlineCreatorInterface
{

    /**
     * @param $searchResult
     * @param SearchQuery $searchQuery
     * @param string $type
     * @return OnlineResultInstance
     * @throws OnlineBookingSearchException
     */
    public function createCommon($searchResult, SearchQuery $searchQuery, string $type): OnlineResultInstance
    {
        $instance = $this->create($searchResult, $searchQuery, $type);
        $this->handleAdditional($instance, $searchQuery);

        return $instance;
    }

    /**
     * @param OnlineResultInstance $instance
     * @param SearchQuery $searchQuery
     */
    private function handleAdditional(OnlineResultInstance $instance, SearchQuery $searchQuery): void
    {
        $isAdditional = !(
            $instance->getResults()->first()->getBegin() == $searchQuery->begin &&
            $instance->getResults()->first()->getEnd() == $searchQuery->end);

        if ($isAdditional) {
            $instance->setType('additional');
        }
    }

    /**
     * @param $searchResult
     * @param SearchQuery $searchQuery
     * @param string $type
     * @return OnlineResultInstance
     * @throws OnlineBookingSearchException
     */
    public function createSpecial($searchResult, SearchQuery $searchQuery, string $type): OnlineResultInstance
    {
        $instance = $this->create($searchResult, $searchQuery, $type);
        $instance->setSpecial($searchQuery->getSpecial());

        return $instance;
    }
    /**
     * @param array $searchResult
     * @param SearchQuery $searchQuery
     * @param string $type
     * @return OnlineResultInstance
     * @throws OnlineBookingSearchException
     */
    private function create($searchResult, SearchQuery $searchQuery): OnlineResultInstance
    {
        if ($searchResult instanceof SearchResult) {
            $instance = $this->createInstance($searchResult->getRoomType(), [$searchResult], $searchQuery, $type);
        } elseif (is_array($searchResult)) {
            $roomType = $searchResult['roomType'];
            $results = $searchResult['results'];
            $instance = $this->createInstance($roomType, $results, $searchQuery, $type);
        } else {
            throw new OnlineBookingSearchException('Cannot create OnlineResult from searchResult');
        }

        return $instance;
    }


    /**
     * @param $roomType
     * @param array $results
     * @param SearchQuery $searchQuery
     * @param string $type
     * @return OnlineResultInstance
     */
    private function createInstance($roomType, array $results, SearchQuery $searchQuery, string $type): OnlineResultInstance
    {
        /** @var OnlineResultInstance $instance */
        $instance = new OnlineResultInstance();
        $instance->setType($type);
        if ($roomType instanceof RoomType || $roomType instanceof RoomTypeCategory) {
            $instance->setRoomType($roomType);
        }
        foreach ($results as $searchResult) {
            $instance->addResult($searchResult);
        }
        $instance->setQuery(clone $searchQuery);
        if (isset($results[0]) && $results[0] instanceof SearchResult && $results[0]->getQueryId()) {
            $searchResult = $results[0];
            /** @var SearchResult $searchResult */
            $instance->setQueryId($searchResult->getQueryId());
        }

        return $instance;
    }
}