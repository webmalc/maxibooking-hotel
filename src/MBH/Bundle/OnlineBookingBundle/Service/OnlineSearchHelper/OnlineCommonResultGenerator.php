<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;

class OnlineCommonResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'common';

    protected function configureSearch()
    {
        parent::configureSearch();
        $this->search->setWithTariffs();
    }

    protected function resultOnlineInstanceCreator($searchResult): OnlineResultInstance
    {
        $roomType = $searchResult['roomType'];
        $results = $searchResult['results'];
        $instance = $this->createOnlineResultInstance($roomType, $results);

        return $instance;
    }

    protected function createOnlineResultInstance($roomType, $results): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results);

        $isAdd = !(
            $instance->getResults()->first()->getBegin() == $this->searchQuery->begin &&
            $instance->getResults()->first()->getEnd() == $this->searchQuery->end);

        if ($isAdd) {
            $type = 'additional';
        } else {
            $type = static::TYPE;
        }
        $instance->setType($type);

        return $instance;
    }



}