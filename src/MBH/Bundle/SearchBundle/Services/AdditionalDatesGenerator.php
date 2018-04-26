<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Restriction;

class AdditionalDatesGenerator
{

    /** @var int */
    private const LOOP_LIMIT = 100;

    /** @var DocumentManager */
    private $dm;

    /** @var array */
    private $restrictions;

    /**
     * AdditionalDatesGenerator constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function generate(
        \DateTime $begin,
        \DateTime $end,
        ?int $additionalBegin = null,
        ?int $additionslEnd = null,
        array $tariffsIds,
        array $roomTypeIds
    ): array {
        $this->restrictions = $this->getRestrictions($tariffsIds, $roomTypeIds);
        $begins = $this->dateGenerate($begin, $additionalBegin, null, 'arrival');
        $ends = $this->dateGenerate($end, $additionslEnd, null, 'departure');

        return $this->combineDates($begins, $ends);
    }

    private function dateGenerate(\DateTime $date, int $range = null, string $direction = null, string $inOrOut): array
    {
        $dates = [];
        if (null === $range) {
            $range = 0;
        }
        if (!$direction) {
            $dates = array_merge($dates, $this->dateGenerate($date, $range, 'up', $inOrOut));
            $dates = array_merge($dates, $this->dateGenerate($date, $range, 'down', $inOrOut));
            if (!$this->isRestricted($date, $inOrOut)) {
                $dates = array_merge($dates, [$date]);
            }


            return $dates;
        }

        $directions = ['up' => '+', 'down' => '-'];

        $clonedDate = clone $date;
        $loop = 0;
        while (0 !== $range) {
            $loop++;
            $clonedDate->modify($directions[$direction].' 1 day');
            if (!$this->isRestricted($clonedDate, $inOrOut)) {
                $dates[] = clone $clonedDate;
                $range--;
            }

            if (self::LOOP_LIMIT > $loop) {
                break;
            }
        }

        return $dates;
    }

    private function isRestricted(\DateTime $date, string $inOrOut): bool
    {
        if (isset($this->restrictions[$inOrOut])) {
            return \in_array($date->format('d-m-Y'), $this->restrictions[$inOrOut], true);
        }

        return false;
    }

    private function getRestrictions(array $tariffIds, array $roomTypeIds): array
    {
//        $restrictions = $this->dm->getRepository(Restriction::class)->findAll();
//        $qb = $this->dm->createQueryBuilder(Restriction::class);
//        if ($tariffIds) {
//            $qb->field('tariff.id')->in($tariffIds);
//        }
//        if ($roomTypeIds) {
//            $qb->field('roomType.id')->in($roomTypeIds);
//        }
//        $restrictions = $qb->hydrate(false)->getQuery()->execute()->toArray();
        $restrictions = $this->dm->getRepository(Restriction::class)->fetchInOut();

        return $restrictions;
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