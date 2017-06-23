<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\Sorters;


use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\SpecialPrice;

class SpecialSorterPrice implements SorterInterface
{
    public function sort(array $specials)
    {
        uasort(
            $specials,
            function ($a, $b) {
                /** @var Special $a */
                /** @var SpecialPrice $c */
                return reset($a['prices']) <=> reset($b['prices']);
            }
        );

        return $specials;
    }

}