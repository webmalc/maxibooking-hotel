<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Services\DateSorters\DateSorterInterface;
use MBH\Bundle\SearchBundle\Services\DateSorters\SorterFactory;

class AdditionalDatesGenerator
{
    public const DURATION_DELTA = 3;

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int|null $additionalBegin
     * @param int|null $additionalEnd
     * @param null|string $sorterName
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    public function generate(
        \DateTime $begin,
        \DateTime $end,
        ?int $additionalBegin = null,
        ?int $additionalEnd = null,
        ?string $sorterName = 'nearestFirst'
    ): array {
        $combinedDates = $this->generateDates($begin, $end, $additionalBegin, $additionalEnd);
        $sorter = SorterFactory::createSorter($sorterName);

        return $sorter->sort($begin, $end, $combinedDates);
    }

    private function generateDates(\DateTime $begin, \DateTime $end, ?int $beginRange = null, ?int $endRange = null): array
    {
        $dates = [];
        if (null === $beginRange) {
            $beginRange = 0;
        }
        if (null === $endRange) {
            $endRange = $beginRange;
        }

        $searchedDuration = (int) $begin->diff($end)->format('%a');

        foreach (new \DatePeriod((clone $begin)->modify("- ${beginRange} days"), \DateInterval::createFromDateString('1 day'), (clone $begin)->modify("+ ${beginRange} days +1 day")) as $beginDay) {
            foreach (new \DatePeriod((clone $end)->modify("- ${endRange} days"), \DateInterval::createFromDateString('1 day'), (clone $end)->modify("+ ${endRange} days +1 day")) as $endDay) {
                if ($beginDay < $endDay && $this->isAppropriateDuration($beginDay, $endDay, $searchedDuration)) {
                    /** @var \DateTime $beginDay */
                    /** @var \DateTime $endDay */
                    $dates[$beginDay->format('d-m-Y') . '_' . $endDay->format('d-m-Y')] = [
                        'begin' => $beginDay,
                        'end' => $endDay
                    ];
                }
            }
        }

        return $dates;
    }

    private function isAppropriateDuration(\DateTime $begin, \DateTime $end, int $searchedDuration): bool
    {
        $currentDuration = (int) $begin->diff($end)->format('%a');


        return (($searchedDuration + self::DURATION_DELTA) >= $currentDuration) && ($currentDuration >= ($searchedDuration - self::DURATION_DELTA));
    }
}