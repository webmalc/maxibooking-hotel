<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\Sorters;


interface SorterInterface
{
    public function sort(array $specials);
}