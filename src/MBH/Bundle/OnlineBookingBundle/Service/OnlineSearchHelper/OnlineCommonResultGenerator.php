<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Special;

class OnlineCommonResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'common';

    protected function searchByFormData(OnlineSearchFormData $formData): ArrayCollection
    {
        $result = new ArrayCollection();
        if ($formData->getSpecial()) {
            return new ArrayCollection();
        }

        $searchQuery = $this->initSearchQuery($formData);
        if ($this->options['add_search_dates']) {
            $range = $this->options['add_search_dates'];
            $searchQuery->range = $range;
            $this->search->setAdditionalDates($range);
        }
        $this->search->setWithTariffs();
        $searchResults = $this->search($searchQuery);
        if (count($searchResults)) {
            foreach ($searchResults as $searchResult) {
                $onlineInstance = $this->resultOnlineInstanceCreator($searchResult, $searchQuery);
                $result->add($onlineInstance);
            }
        }

        return $result;

    }

    protected function createOnlineResultInstance($roomType, array $results, SearchQuery $searchQuery): OnlineResultInstance
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

    protected function resultsHandle(ArrayCollection $results): ArrayCollection
    {
        if (!$results->isEmpty()) {
            $searchQuery = $results->first()->getQuery();
            $results = $this->separateByAdditionalDays($searchQuery, $results);
        }

        /*$this->filterByCapacity();*/
        return parent::resultsHandle($results);
    }


}