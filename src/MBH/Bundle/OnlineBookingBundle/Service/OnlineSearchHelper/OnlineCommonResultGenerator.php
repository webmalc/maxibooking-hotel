<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;

use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Special;

class OnlineCommonResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'common';

    protected function search(SearchQuery $searchQuery, $roomType = null, Special $special = null)
    {
        if ($special) {
            return [];
        }

        if ($this->options['add_search_dates']) {
            $range = $this->options['add_search_dates'];
            $searchQuery->range = $range;
            $this->search->setAdditionalDates($range);
        }
        $this->search->setWithTariffs();

        return parent::search($searchQuery, $roomType, $special);
    }

    protected function createOnlineResultInstance($roomType, $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results, $searchQuery);

        $isAdditional = !(
            $instance->getResults()->first()->getBegin() == $searchQuery->begin &&
            $instance->getResults()->first()->getEnd() == $searchQuery->end);

        if ($isAdditional && static::TYPE === $instance->getType()) {
            $instance->setType('additional');
        }

        return $instance;
    }

    protected function resultsHandle(SearchQuery $searchQuery): void
    {
        $this->separateByAdditionalDays($searchQuery);
        $this->filterByCapacity();
        parent::resultsHandle($searchQuery);
    }


}