<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\Sorters;


class SpecialNoSorter implements SorterInterface
{
    public function sort(array $specials)
    {
        return $specials;
    }

}