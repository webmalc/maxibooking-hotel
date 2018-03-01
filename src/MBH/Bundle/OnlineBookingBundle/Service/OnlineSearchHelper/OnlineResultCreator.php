<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

/**
 * Class OnlineResultCreator
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper
 */
class OnlineResultCreator
{
    /**
     * @param array $searchResult
     * @param SearchQuery $searchQuery
     * @return OnlineResultInstance
     * @throws OnlineBookingSearchException
     */
    public function create(array $searchResult, SearchQuery $searchQuery): OnlineResultInstance
    {
        if ($searchResult instanceof SearchResult) {
            $instance = $this->createInstance($searchResult->getRoomType(), [$searchResult], $searchQuery);
        } elseif (is_array($searchResult)) {
            $roomType = $searchResult['roomType'];
            $results = $searchResult['results'];
            $instance = $this->createInstance($roomType, $results, $searchQuery);
        } else {
            throw new OnlineBookingSearchException('Cannot create OnlineResult from searchResult');
        }

        return $instance;
    }

    /**
     * @param $roomType
     * @param array $results
     * @param SearchQuery $searchQuery
     * @return SearchResult
     */
    private function createInstance($roomType, array $results, SearchQuery $searchQuery): OnlineResultInstance
    {

    }
}