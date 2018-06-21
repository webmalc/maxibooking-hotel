<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters;


use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

/**
 * Class SpecialCreator
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters
 */
class SpecialCreator extends AbstractCreator
{

    /** @var string */
    protected const TYPE = "special";

    /**
     * @param $searchResult
     * @param SearchQuery $searchQuery
     * @return OnlineResultInstance|null
     * @throws \MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException
     */
    public function create($searchResult, SearchQuery $searchQuery): ?OnlineResultInstance
    {
        $instance = $this->doCreate($searchResult, $searchQuery);
        $instance->setSpecial($searchQuery->getSpecial());

        return $instance;
    }

}