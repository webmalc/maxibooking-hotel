<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;

class AdditionalSorter implements OnlineSorterInterface
{
    public function sort(array $data, OnlineSearchFormData $formData = null): array
    {
        usort(
            $data,
            function ($resA, $resB) {
                /** @var OnlineResultInstance $resA */
                /** @var OnlineResultInstance $resB */
                $priceA = $resA->getResults()->first()->getPrices();
                $priceB = $resB->getResults()->first()->getPrices();

                return reset($priceA) <=> reset($priceB);
            }
        );

        return $data;
    }

}