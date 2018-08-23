<?php


namespace MBH\Bundle\SearchBundle\Services\DateSorters;


class NearestFirstSorter implements DateSorterInterface
{
    public function sort(\DateTime $begin, \DateTime $end, array $dates): array
    {
        uasort(
            $dates,
            function ($lengthDeltaA, $lengthDeltaB) use ($begin, $end) {
                /** @var \DateTime $beginA */
                $beginA = $lengthDeltaA['begin'];
                /** @var \DateTime $endA */
                $endA = $lengthDeltaA['end'];
                /** @var \DateTime $beginB */
                $beginB = $lengthDeltaB['begin'];
                /** @var \DateTime $endB */
                $endB = $lengthDeltaB['end'];

                $needleLength = (int)$begin->diff($end)->format('%a');

                $lengthA = (int)$beginA->diff($endA)->format('%a');
                $lengthB = (int)$beginB->diff($endB)->format('%a');

                $lengthDeltaA = abs($lengthA - $needleLength);
                $lengthDeltaB = abs($lengthB - $needleLength);

                //Сначала дельта длины брони. Выше в списке та дельта, что минимальна.
                $result = $lengthDeltaA <=> $lengthDeltaB;
                if ($result === 0) {
                    $beginDeltaA = (int)$begin->diff($beginA)->format('%a');
                    $endDeltaA = (int)$end->diff($endA)->format('%a');

                    $beginDeltaB = (int)$begin->diff($beginB)->format('%a');
                    $endDeltaB = (int)$end->diff($endB)->format('%a');

                    $dateDeltaA = $beginDeltaA + $endDeltaA;
                    $dateDeltaB = $beginDeltaB + $endDeltaB;
                    //Среди одинаковых - выше будет та, у которой общая разница в датах минимальна
                    $result = $dateDeltaA <=> $dateDeltaB;

                    if ($result === 0) {
                        // Среди одинаковых более ранняя дата.

                        $result = $beginDeltaA <=> $beginDeltaB;
                    }

                }
                return $result;
            }
        );

        return $dates;
    }
}