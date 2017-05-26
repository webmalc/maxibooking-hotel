<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Special;

class OnlineSpecialResultGenerator extends AbstractResultGenerator
{
    protected const TYPE = 'special';

    const SPECIAL_LIMIT = 0;

    protected function createOnlineResultInstance($roomType, array $results, SearchQuery $searchQuery): OnlineResultInstance
    {
        $instance = parent::createOnlineResultInstance($roomType, $results, $searchQuery);
        $instance->setSpecial($searchQuery->getSpecial());

        return $instance;
    }

    protected function searchByFormData(OnlineSearchFormData $formData): ArrayCollection
    {
        if (!$this->options['show_specials'] || $this->originalFormData->isAddDates()) {
            return new ArrayCollection();
        }
        $results = new ArrayCollection();
        $special = $formData->getSpecial();
        $roomType = $formData->getRoomType();

        if ($special && $roomType) {
            if (!$special->getRemain() || !$special->getIsEnabled()) {
                return new ArrayCollection();
            }
            $searchQuery = $this->initSearchQuery($formData);
            $searchQuery->begin = $special->getBegin();
            $searchQuery->end = $special->getEnd();
            $searchQuery->forceRoomTypes = true;
            $searchQuery->setPreferredVirtualRoom($special->getVirtualRoom());
            $searchResult = $this->search($searchQuery);

            if ($searchResult && !$this->isVirtualRoomIsNull(reset($searchResult))) {
                $onlineInstance = $this->resultOnlineInstanceCreator(reset($searchResult), $searchQuery);
                $results->add($onlineInstance);
            }
        } else {
            /** @var Cursor $specials */
            $searchQuery = $this->initSearchQuery($formData);
            $specials = $this->search->searchStrictSpecials($searchQuery);
            $specials = $this->filterSpecials($specials->toArray());
            if (count($specials)) {
                $count = 0;
                foreach ($specials as $special) {
                    /** @var Special $special */
                    //Тут рекурсия
                    $newFormData = clone $formData;
                    $newFormData->setSpecial($special);
                    $newFormData->setRoomType($special->getVirtualRoom()->getRoomType());
                    $onlineInstance = $this->searchByFormData($newFormData)->first();
                    if ($onlineInstance) {
                        $results->add($onlineInstance);
                        $count++;
                    }

                    $specialLimit = $this->options['show_special_restrict']??self::SPECIAL_LIMIT;

                    if ($specialLimit && $count >= $specialLimit) {
                        break;
                    }
                }

            }
        }

        return $results;
    }

    private function isVirtualRoomIsNull(SearchResult $searchResult)
    {
        return $searchResult->getVirtualRoom() === null ? true: false;
    }

    private function filterSpecials(array $specials)
    {
        if (count($specials)) {
            uasort(
                $specials,
                function ($a, $b) {
                    $needleBegin = $this->originalFormData->getBegin();
                    $diffA = $needleBegin->diff($a->getBegin());
                    $diffB = $needleBegin->diff($b->getBegin());
                    $diffDateA = (int)$diffA->format('%d');
                    $diffDateB = (int)$diffB->format('%d');
                    if ($diffDateA < $diffDateB) {
                        return -1;
                    } elseif($diffDateA > $diffDateB) {
                        return 1;
                    } else {
                        $priceA = $a->getPrices()->toArray()[0]->getPrices();
                        $priceB = $b->getPrices()->toArray()[0]->getPrices();

                        return reset($priceA) <=> reset($priceB);
                    }
                }
            );
        }

        return $specials;
    }


}