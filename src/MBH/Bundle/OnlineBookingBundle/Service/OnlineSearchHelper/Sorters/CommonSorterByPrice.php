<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


use MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\OnlineResultInstance;

class CommonSorterByPrice implements OnlineSorterInterface
{
    public function sort(array $data): array
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