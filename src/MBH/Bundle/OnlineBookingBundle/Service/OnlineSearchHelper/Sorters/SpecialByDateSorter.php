<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;

class SpecialByDateSorter implements OnlineSorterInterface
{
    public function sort(array $data, OnlineSearchFormData $formData = null): array
    {
        uasort(
            $data,
            function ($a, $b) use ($formData) {
                $needleBegin = $formData->getBegin();
                /** @var OnlineResultInstance $a */
                /** @var OnlineResultInstance $b */
                $diffA = $needleBegin->diff($a->getSpecial()->getBegin());
                $diffB = $needleBegin->diff($b->getSpecial()->getBegin());
                $diffDateA = (int)$diffA->format('%d');
                $diffDateB = (int)$diffB->format('%d');
                $result = $diffDateA <=> $diffDateB;
                if ($result === 0) {
                    $daysA = abs((int)$a->getSpecial()->getBegin()->diff($a->getSpecial()->getEnd())->format('%d'));
                    $daysB = abs((int)$b->getSpecial()->getBegin()->diff($b->getSpecial()->getEnd())->format('%d'));
                    $result = $daysB <=> $daysA;
                    if ($result === 0) {
                        $priceA = $a->getSpecial()->getPrices()->toArray()[0]->getPrices();
                        $priceB = $b->getSpecial()->getPrices()->toArray()[0]->getPrices();
                        $result = reset($priceA) <=> reset($priceB);
                    }
                }

                return $result;
            }
        );

        return $data;
    }

}