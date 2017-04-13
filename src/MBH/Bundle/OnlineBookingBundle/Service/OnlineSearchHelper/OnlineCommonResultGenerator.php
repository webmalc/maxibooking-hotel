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

    protected function createOnlineResultInstance($roomType, $results): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results);

        $isAdditional = !(
            $instance->getResults()->first()->getBegin() == $this->searchQuery->begin &&
            $instance->getResults()->first()->getEnd() == $this->searchQuery->end);

        if ($isAdditional && static::TYPE === $instance->getType()) {
            $instance->setType('additional');
        }

        return $instance;
    }



}