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
                if (isset($a['default_price']) && $a['prices'][$a['default_price']]) {
                    $priceA = $a['prices'][$a['default_price']];
                }

                if (isset($b['default_price']) && $b['prices'][$b['default_price']]) {
                    $priceB = $b['prices'][$b['default_price']];
                }
                return ($priceA ?? reset($a['prices'])) <=> ($priceB ?? reset($b['prices']));
            }
        );

        return $specials;
    }

}