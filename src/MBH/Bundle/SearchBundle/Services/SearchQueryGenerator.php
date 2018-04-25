<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;

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
     * @throws SearchQueryGeneratorException
     */
    public function generate(SearchConditions $conditions): void
    {
        try {
            $searchQueries = $this->prepareConditionsForSearchQueries($conditions);
        } catch (MongoDBException $e) {
            throw new SearchQueryGeneratorException('Error in Search Query Generator'.$e->getMessage());
        }

    }


    /**
     * @param SearchConditions $conditions
     * @return SearchQuery[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function prepareConditionsForSearchQueries(SearchConditions $conditions): array
    {
        $variants = [];
        $begins = $this->generateDaysWithRange($conditions->getBegin(), $conditions->getAdditionalBegin());
        $ends = $this->generateDaysWithRange($conditions->getEnd(), $conditions->getAdditionalEnd());
        $hotelIds = $this->getEntryIds($conditions->getHotels());
        $tariffIds = $this->getTariffIds($conditions->getTariffs(), $hotelIds, $conditions->isOnline());
        $roomTypeIds = $this->getRoomTypeIds($conditions->getRoomTypes(), $hotelIds);

        $dates = $this->combineDates($begins, $ends);
        $tariffRoomTypeCombined = $this->combineTariffWithRoomType($roomTypeIds, $tariffIds);


        //
//        foreach ($begins as $arrival) {
//            foreach ($ends as $departure) {
//                if ($arrival < $departure) {
//                    $dates[$arrival->format('d-m-Y').'_'.$departure->format('d-m-Y')] = [$arrival, $departure];
//                }
//            }
//        }

        return $variants;
    }


    private function generateDaysWithRange(\DateTime $date, int $range = null, string $direction = null): array
    {
        $dates = [];
        if (null === $range) {
            $range = 0;
        }
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

    private function getEntryIds(ArrayCollection $entry): array
    {
        return Helper::toIds($entry);
    }

    /**
     * @param ArrayCollection|Tariff[] $tariffs
     * @param array $hotelIds
     * @param bool $isOnline
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    private function getTariffIds(ArrayCollection $tariffs, array $hotelIds, bool $isOnline): array
    {
        $tariffIds = [];
        if ($tariffs->count()) {
            foreach ($tariffs as $tariff) {
                $tariffIds[$tariff->getHotel()->getId()][] = $tariff->getId();
            }

            return $tariffIds;
        }
        try {
            $tariffsRaw = $this->dm->getRepository(Tariff::class)->fetchRaw(
                $hotelIds,
                $tariffIds,
                true,
                $isOnline
            );
        } catch (MongoDBException $e) {
            throw new SearchQueryGeneratorException('Error in fetchRaw repo method. '.$e->getMessage());
        }

        if (empty($tariffsRaw)) {
            throw new SearchQueryGeneratorException(
                'Empty Result in Tariff query, cannot create SearchQuery without any Tariff'
            );
        }

        foreach ($tariffsRaw as $tariffId => $tariff) {
            $tariffIds[(string)$tariff['hotel']['$id']][] = $tariffId;
        }

        return $tariffIds;
    }


    /**
     * @param ArrayCollection|RoomType[] $roomTypes
     * @param array $hotelIds
     * @return array
     */
    private function getRoomTypeIds(ArrayCollection $roomTypes, array $hotelIds)
    {
        $roomTypeIds = [];
        if ($roomTypes->count()) {
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[$roomType->getHotel()->getId()][] = $roomType->getId();
            }

            return $roomTypeIds;
        }
        $roomTypeIds = $this->getEntryIds($roomTypes);
        if (!empty($roomTypeIds)) {
            return $roomTypeIds;
        }

        if (empty($roomTypeIds)) {
            $roomTypeIds = $this->dm->getRepository(RoomType::class)->fetchInHotels($roomTypeIds, $hotelIds);
        }

        return $roomTypeIds;
    }

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

    private function combineTariffWithRoomType(array $roomTypeIds, array $tariffIds)
    {

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