<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters;


use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

/**
 * Class CommonCreator
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters
 */
class CommonCreator extends AbstractCreator implements OnlineCreatorInterface
{

    /** @var string */
    protected const TYPE = "common";

    /**
     * @param $searchResult
     * @param SearchQuery $searchQuery
     * @return OnlineResultInstance|null
     * @throws \MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException
     */
    public function create($searchResult, SearchQuery $searchQuery): ?OnlineResultInstance
    {
        return $this->doCreate($searchResult, $searchQuery);
    }

}