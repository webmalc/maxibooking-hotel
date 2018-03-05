<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

abstract class AbstractCreator implements OnlineCreatorInterface
{
    /** @var string */
    protected const TYPE = "abstract";

    /**
     * @param array $searchResult
     * @param SearchQuery $searchQuery
     * @return OnlineResultInstance
     * @throws OnlineBookingSearchException
     */
    protected function doCreate($searchResult, SearchQuery $searchQuery): OnlineResultInstance
    {
        if ($searchResult instanceof SearchResult) {
            $instance = $this->createInstance($searchResult->getRoomType(), [$searchResult], $searchQuery, static::TYPE);
        } elseif (is_array($searchResult)) {
            $roomType = $searchResult['roomType'];
            $results = $searchResult['results'];
            $instance = $this->createInstance($roomType, $results, $searchQuery, static::TYPE);
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
    protected function createInstance($roomType, array $results, SearchQuery $searchQuery, string $type): OnlineResultInstance
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