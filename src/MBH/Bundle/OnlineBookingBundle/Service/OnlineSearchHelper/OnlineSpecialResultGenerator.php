<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PriceBundle\Document\Special;

class OnlineSpecialResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'special';

    const SPECIAL_LIMIT = 10;

    protected function createOnlineResultInstance($roomType, $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results, $searchQuery);
        $instance->setSpecial($results[0]->getPackagePrices($results[0]->getAdults(), $results[0]->getChildren())[0]->getSpecial());


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
            /** @var Cursor $specials */
            $specials = $this->search->searchStrictSpecials($searchQuery);
            $specials = $this->filterSpecials($specials->toArray());
            if (count($specials)) {
                $count = 0;
                foreach ($specials as $special) {
                    /** @var Special $special */
                    //Тут рекурсия
                    $searchResult = $this->search($searchQuery, $special->getRoomTypes()->first(), $special);
                    if ($searchResult) {
                        $results = array_merge($results, $searchResult);
                        $count++;
                    }
                    if (self::SPECIAL_LIMIT && $count >= self::SPECIAL_LIMIT) {
                        break;
                    }
                }

            }
        }

        return $results;
    }

    private function filterSpecials(array $specials)
    {
        if (count($specials)) {
            uasort(
                $specials,
                function ($a, $b) {
                    $priceA = $a->getPrices()->toArray()[0]->getPrices();
                    $priceB = $b->getPrices()->toArray()[0]->getPrices();

                    return reset($priceA) <=> reset($priceB);
                }
            );
        }

        return $specials;
    }


}