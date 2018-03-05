<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

interface OnlineSorterInterface
{
    public function sort(array $data, OnlineSearchFormData $formData = null): array;
}