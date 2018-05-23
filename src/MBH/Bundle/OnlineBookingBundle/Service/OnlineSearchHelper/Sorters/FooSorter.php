<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

class FooSorter implements OnlineSorterInterface
{
    public function sort(array $data, OnlineSearchFormData $formData = null): array
    {
        return $data;
    }

}