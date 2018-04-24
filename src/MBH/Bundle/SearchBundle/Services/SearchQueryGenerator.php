<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchException;


class SearchQueryGenerator
{

    /** @var string */
    private $searchQueryHash;

    /** @var int */
    private $queuesNum;

    /** @var DocumentManager */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    /**
     * @param SearchConditions $conditions
     * @throws SearchException
     */
    public function generate(SearchConditions $conditions): void
    {
        $combinedData = $this->combineQueries($conditions);

    }

    private function combineQueries(SearchConditions $conditions): array
    {
        $begins = $this->generateDaysWithRange($conditions->getBegin(), $conditions->getAdditionalBegin());
        $ends = $this->generateDaysWithRange($conditions->getEnd(), $conditions->getAdditionalEnd());
        $tariffs = $conditions->getTariffs()->toArray();
        $roomTypes = $conditions->getRoomTypes()->toArray();

        if (empty($tariffs)) {
            $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetch(null, null, true, $conditions->isOnline(), null)->toArray();
        }

        if (empty($roomTypes)) {
            $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findAll();
        }

        $a = 'b';
    }



    private function generateDaysWithRange(\DateTime $date, int $range, string $direction = null): array
    {
        $dates = [];
        if (!$direction) {
            $dates = array_merge($dates, $this->generateDaysWithRange($date, $range, 'up'));
            $dates = array_merge($dates, $this->generateDaysWithRange($date, $range, 'down'));
            $dates = array_merge($dates, [$date]);

            sort($dates);

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
     * @return string
     */
    public function getSearchQueryHash(): string
    {
        return $this->searchQueryHash;
    }

    /**
     * @return int
     */
    public function getQueuesNum(): int
    {
        return $this->queuesNum;
    }


}