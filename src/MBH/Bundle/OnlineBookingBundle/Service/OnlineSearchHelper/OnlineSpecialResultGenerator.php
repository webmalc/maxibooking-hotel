<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


class OnlineSpecialResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'special';

    protected function createOnlineResultInstance($roomType, $results): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results);
        $instance->setSpecial($this->searchQuery->getSpecial());

        return $instance;
    }
}