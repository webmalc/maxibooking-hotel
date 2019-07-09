<?php


namespace MBH\Bundle\SearchBundle\Services;


use DateInterval;
use DatePeriod;
use DateTime;
use MBH\Bundle\SearchBundle\Document\SearchConfig;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConfigException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Services\DateSorters\SorterFactory;

/**
 * Class AdditionalDatesGenerator
 * @package MBH\Bundle\SearchBundle\Services
 */
class AdditionalDatesGenerator
{

    /**
     * @var int
     */
    private $positiveDelta;

    /**
     * @var int
     */
    private $negativeDelta;

    /** @var int */
    private $maxPackageLength;

    /**
     * @var int
     */
    private $minPackageLength;
    /**
     * @var SearchConfig
     */
    private $config;
    /**
     * @var int
     */
    private $negativeAddDatesLimit;
    /**
     * @var int
     */
    private $positiveAddDatesLimit;

    /**
     * AdditionalDatesGenerator constructor.
     * @param SearchConfig $config
     */
    public function __construct(SearchConfig $config)
    {
        $this->positiveDelta = $config->getPositivePackageLengthDelta();
        $this->negativeDelta = $config->getNegativePackageLengthDelta();
        $this->maxPackageLength = $config->getMaxAdditionalPackageLength();
        $this->minPackageLength = $config->getMinAdditionalPackageLength();
        $this->positiveAddDatesLimit = $config->getPositiveMaxAdditionalSearchDaysAmount();
        $this->negativeAddDatesLimit = $config->getNegativeMaxAdditionalSearchDaysAmount();
    }


    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @param int|null $additionalBegin
     * @param int|null $additionalEnd
     * @param null|string $sorterName
     * @return array
     * @throws SearchConfigException
     * @throws SearchQueryGeneratorException
     */
    public function generate(
        DateTime $begin,
        DateTime $end,
        ?int $additionalBegin = null,
        ?int $additionalEnd = null,
        ?string $sorterName = 'nearestFirst'
    ): array {
        $combinedDates = $this->generateDates($begin, $end, $additionalBegin, $additionalEnd);
        $sorter = SorterFactory::createSorter($sorterName);

        /** TODO: Сортеру тут делать нечего, нужно его переносить в место где генерятся группы запросов */
        return $sorter->sort($begin, $end, $combinedDates);
    }

    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @param int|null $beginRange
     * @param int|null $endRange
     * @return array
     * @throws SearchConfigException
     */
    private function generateDates(DateTime $begin, DateTime $end, ?int $beginRange = null, ?int $endRange = null): array
    {
        $dates = [];
        if (null === $beginRange) {
            $beginRange = 0;
        }
        if (null === $endRange) {
            $endRange = $beginRange;
        }

        $beginRange = min($beginRange, $this->positiveAddDatesLimit);
        $endRange = min($endRange, $this->negativeAddDatesLimit);

        $searchedDuration = (int) $begin->diff($end)->format('%a');

        foreach (new DatePeriod((clone $begin)->modify("- ${beginRange} days"), DateInterval::createFromDateString('1 day'), (clone $begin)->modify("+ ${beginRange} days +1 day")) as $beginDay) {
            foreach (new DatePeriod((clone $end)->modify("- ${endRange} days"), DateInterval::createFromDateString('1 day'), (clone $end)->modify("+ ${endRange} days +1 day")) as $endDay) {
                if ($beginDay < $endDay && $this->isAppropriateDuration($beginDay, $endDay, $searchedDuration)) {
                    /** @var DateTime $beginDay */
                    /** @var DateTime $endDay */
                    $dates[$beginDay->format('d-m-Y') . '_' . $endDay->format('d-m-Y')] = [
                        'begin' => $beginDay,
                        'end' => $endDay
                    ];
                }
            }
        }

        return $dates;
    }

    /**
     * @param DateTime $begin
     * @param DateTime $end
     * @param int $searchedDuration
     * @return bool
     */
    private function isAppropriateDuration(DateTime $begin, DateTime $end, int $searchedDuration): bool
    {


        $currentDuration = (int) $begin->diff($end)->format('%a');

        $isPositiveDeltaSatisfy = $this->isPositiveDeltaSatisfy($searchedDuration, $currentDuration);
        $isNegativeDeltaSatisfy = $this->isNegativeDeltaSatisfy($searchedDuration, $currentDuration);
        $isMaxPackageSatisfy = $this->isMaximalPackageLengthSatisfy($currentDuration);
        $isMinPackageSatisfy = $this->isMinimalPackageLengthSatisfy($currentDuration);

        return $isPositiveDeltaSatisfy && $isNegativeDeltaSatisfy && $isMinPackageSatisfy && $isMaxPackageSatisfy;
    }

    /**
     * @param int $searchedDuration
     * @param int $currentDuration
     * @return bool
     */
    private function isPositiveDeltaSatisfy(int $searchedDuration, int $currentDuration): bool
    {
        return ($searchedDuration + $this->positiveDelta) >= $currentDuration;
    }

    /**
     * @param int $searchedDuration
     * @param int $currentDuration
     * @return bool
     */
    private function isNegativeDeltaSatisfy(int $searchedDuration, int $currentDuration): bool
    {
        return $currentDuration >= ($searchedDuration - $this->negativeDelta);
    }

    /**
     * @param int $currentDuration
     * @return bool
     */
    private function isMinimalPackageLengthSatisfy(int $currentDuration): bool
    {
        return $currentDuration >= $this->minPackageLength;
    }

    /**
     * @param int $currentDuration
     * @return bool
     */
    private function isMaximalPackageLengthSatisfy(int $currentDuration): bool
    {
        return $currentDuration <= $this->maxPackageLength;
    }

}