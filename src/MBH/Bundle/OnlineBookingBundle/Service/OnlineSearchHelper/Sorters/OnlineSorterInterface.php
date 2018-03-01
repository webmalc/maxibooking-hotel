<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\Sorters;


interface OnlineSorterInterface
{
    public function sort(array $data): array;
}