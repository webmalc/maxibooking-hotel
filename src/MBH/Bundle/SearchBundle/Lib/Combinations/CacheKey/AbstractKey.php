<?php


namespace MBH\Bundle\SearchBundle\Lib\Combinations\CacheKey;


use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Services\Search\Determiners\Occupancies\CacheKeyOccupancyDetermineInterface;

abstract class AbstractKey implements CacheKeyInterface
{
    /**
     * @var CacheKeyOccupancyDetermineInterface
     */
    protected $determiner;

    /**
     * AbstractKey constructor.
     * @param CacheKeyOccupancyDetermineInterface $determiner
     */
    public function __construct(CacheKeyOccupancyDetermineInterface $determiner)
    {
        $this->determiner = $determiner;
    }


    /**
     * @param SearchQuery $searchQuery
     * @return string
     */
    protected function getSharedPartKey(SearchQuery $searchQuery): string
    {
        $key = '';
        $key .= $searchQuery->getBegin()->format('d.m.Y') . '_' . $searchQuery->getEnd()->format('d.m.Y');
        $key .= '_' . $searchQuery->getRoomTypeId();
        $key .= '_' . $searchQuery->getTariffId();

        return $key;
    }
}

//13.09.2018_27.09.2018_5704ed0a74eb53be118b48e2_571779d074eb53862e8b4578_1_1