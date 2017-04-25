<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Special;

class OnlineSpecialResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'special';

    protected function createOnlineResultInstance($roomType, $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results, $searchQuery);
        $instance->setSpecial($searchQuery->getSpecial());

        return $instance;
    }

    protected function search(SearchQuery $searchQuery, $roomType = null, Special $special = null)
    {
        $results = [];
        if ($special && $roomType) {
            if (!$special->getRemain() || !$special->getIsEnabled()) {
                return [];
            }
            $searchQuery->begin = $special->getBegin();
            $searchQuery->end = $special->getEnd();
            $searchQuery->setSpecial($special);
            $searchQuery->roomTypes = $this->helper->toIds([$roomType]);
            $searchQuery->forceRoomTypes = true;
            $searchQuery->setPreferredVirtualRoom($special->getVirtualRoom());

            $results = array_merge(parent::search($searchQuery, $roomType, $special));
        } else {
            $specials = $this->search->searchStrictSpecials($searchQuery);
            if (count($specials)) {
                foreach ($specials as $special) {
                    /** @var Special $special */
                    //Тут рекурсия
                    $results = array_merge($this->search($searchQuery, $special->getRoomTypes()->first(), $special));
                }

            }
        }

        return $results;
    }
}