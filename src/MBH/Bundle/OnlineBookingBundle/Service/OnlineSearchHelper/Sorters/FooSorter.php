<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


class FooSorter implements OnlineSorterInterface
{
    public function sort(array $data): array
    {
        return $data;
    }

}