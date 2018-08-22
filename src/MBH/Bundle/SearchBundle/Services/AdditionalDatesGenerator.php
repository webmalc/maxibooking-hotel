<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Restriction;

class AdditionalDatesGenerator
{

    public function generate(
        \DateTime $begin,
        \DateTime $end,
        ?int $additionalBegin = null,
        ?int $additionalEnd = null
    ): array {
        $begins = $this->dateGenerate($begin, $additionalBegin);
        $ends = $this->dateGenerate($end, $additionalEnd);

        return $this->combineDates($begins, $ends);
    }

    private function dateGenerate(\DateTime $date, int $range = null, string $direction = null): array
    {
        $dates = [];
        if (null === $range) {
            $range = 0;
        }
        if (!$direction) {
            $dates = array_merge($dates, $this->dateGenerate($date, $range, 'up'));
            $dates = array_merge($dates, $this->dateGenerate($date, $range, 'down'));
            array_unshift($dates, $date);

            return $dates;
        }

        $directions = ['up' => '+', 'down' => '-'];

        $clonedDate = clone $date;
        while (0 !== $range) {
            $clonedDate->modify($directions[$direction].' 1 day');
            $dates[] = clone $clonedDate;
            $range--;
        }

        return $dates;
    }


    /**
     * @param array $begins
     * @param array $ends
     * @return array
     */
    private function combineDates(array $begins, array $ends): array
    {
        $dates = [];
        foreach ($begins as $begin) {
            foreach ($ends as $end) {
                if ($begin < $end) {
                    $dates[$begin->format('d-m-Y').'_'.$end->format('d-m-Y')] = [
                        'begin' => $begin,
                        'end' => $end,
                    ];
                }
            }
        }

        return $dates;
    }


}