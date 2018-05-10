<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters;


use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

/**
 * Class AdditionalCreator
 * @package MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\ResultCreaters
 */
class AdditionalCreator extends AbstractCreator implements OnlineCreatorInterface
{
    /** @var string */
    protected const TYPE = "additional";

    /**
     * @param $searchResult
     * @param SearchQuery $searchQuery
     * @return OnlineResultInstance|null
     * @throws \MBH\Bundle\OnlineBookingBundle\Lib\Exceptions\OnlineBookingSearchException
     */
    public function create($searchResult, SearchQuery $searchQuery): ?OnlineResultInstance
    {
        /** TODO: SEPARATE! */
        $instance = $this->doCreate($searchResult, $searchQuery);

        return $this->isAdditionalResult($instance, $searchQuery) ? $instance : null;
    }

    private function isAdditionalResult(OnlineResultInstance $instance, SearchQuery $searchQuery)
    {
        $isAdditional = !(
            $instance->getResults()->first()->getBegin() == $searchQuery->begin &&
            $instance->getResults()->first()->getEnd() == $searchQuery->end);

        return $isAdditional;
    }

}