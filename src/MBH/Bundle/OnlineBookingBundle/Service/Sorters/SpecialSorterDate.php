<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\Sorters;

class SpecialSorterDate implements SorterInterface
{
    public function sort(array $specials)
    {
        uasort(
            $specials,
            function ($a, $b) {
                $beginA = $a['special']->getBegin();
                $beginB = $b['special']->getBegin();
                $result = $beginA <=> $beginB;
                if ($result === 0) {
                    $result = reset($a['prices']) <=> reset($b['prices']);
                }

                return $result;
            }
        );

        return $specials;
    }

}