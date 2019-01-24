<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\OccupancyDeterminerFactory;

class NoChildrenAgeCacheKey extends AbstractKey
{

    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getKey(SearchQuery $searchQuery): string
    {
        $occupancies = $this->determiner->determine($searchQuery, OccupancyDeterminerFactory::COMMON_DETERMINER);
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_' . $occupancies->getAdults();
        $key .= '_' . $occupancies->getChildren();

        return $key;
    }

    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    public function getWarmUpKey(SearchQuery $searchQuery): string
    {
        $occupancies = $this->determiner->determine($searchQuery, OccupancyDeterminerFactory::WARM_UP_DETERMINER);
        $key = $this->getSharedPartKey($searchQuery);
        $key .= '_' . $occupancies->getAdults();
        $key .= '_' . $occupancies->getChildren();

        return $key;
    }

    /**
     * @param string $key
     * @return array
     * @throws \Exception
     */
    public function extractWarmUpKey(string $key): array
    {
        [$begin, $end, $roomTypeId, $tariffId, $adults, $children] = explode('_', $key);

        return [
            'begin' => new \DateTime($begin),
            'end' => new \DateTime($end),
            'roomTypeId' => $roomTypeId,
            'tariffId' => $tariffId,
            'combination' => [
                'adults' => $adults,
                'children' => $children,
            ],
        ];
    }


}