<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;

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
        /** save query in db */
        $searchQuery->setSave(true);

        if ($this->options['add_search_dates'] && $formData->isAddDates()) {
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

    /**
     * Divide results to match and additional dates
     * @param SearchQuery $searchQuery
     * @param ArrayCollection $results
     * @return ArrayCollection
     */
    protected function separateByAdditionalDays(SearchQuery $searchQuery, ArrayCollection $results): ArrayCollection
    {

        $result = [];
        foreach ($results as $resultInstance) {
            /** @var OnlineResultInstance $resultInstance */
            $groups = [];

            foreach ($resultInstance->getResults() as $keyNeedleInstance => $searchNeedleInstance) {
                /** @var SearchResult $searchNeedleInstance */
                $needle = $searchNeedleInstance->getBegin()->format('dmY').$searchNeedleInstance->getEnd()->format(
                        'dmY'
                    );
                foreach ($resultInstance->getResults() as $searchKey => $searchInstance) {
                    /** @var SearchResult $searchInstance */
                    $hayStack = $searchInstance->getBegin()->format('dmY').$searchInstance->getEnd()->format('dmY');
                    if ($needle == $hayStack) {
                        $groups[$needle][$searchKey] = $searchInstance;
                    }
                }
            }
            foreach ($groups as $group) {
                $instance = $this->createOnlineResultInstance($resultInstance->getRoomType(), array_values($group), $searchQuery);
                //Грязных хак для показа только результатов с доп датами
                if ($this->originalFormData->isAddDates() && $instance->getType() === 'common') {
                    continue;
                }
                $result[] = $instance;
            }
        }

        usort(
            $result,
            function ($resA, $resB) {
                /** @var OnlineResultInstance $resA */
                /** @var OnlineResultInstance $resB */
                $priceA = $resA->getResults()->first()->getPrices();
                $priceB = $resB->getResults()->first()->getPrices();

                return reset($priceA) <=> reset($priceB);
            }
        );

        return new ArrayCollection($result);
    }


}