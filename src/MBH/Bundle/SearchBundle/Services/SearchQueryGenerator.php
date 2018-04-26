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

    /** @var AdditionalDatesGenerator */
    private $addDatesGenerator;

    public function __construct(DocumentManager $dm, AdditionalDatesGenerator $generator)
    {
        $this->dm = $dm;
        $this->addDatesGenerator = $generator;
    }

    /**
     * @param SearchConditions $conditions
     * @throws SearchQueryGeneratorException
     */
    public function generate(SearchConditions $conditions): void
    {
        $combinations = $this->prepareConditionsForSearchQueries($conditions);

    }


    /**
     * @param SearchConditions $conditions
     * @return SearchQuery[]
     * @throws SearchQueryGeneratorException
     */
    private function prepareConditionsForSearchQueries(SearchConditions $conditions): array
    {
        $hotelIds = $this->getEntryIds($conditions->getHotels());
        $tariffIds = $this->getTariffIds($conditions->getTariffs(), $hotelIds, $conditions->isOnline());
        $roomTypeIds = $this->getRoomTypeIds($conditions->getRoomTypes(), $hotelIds);

        $dates =
            $this->addDatesGenerator->generate(
                $conditions->getBegin(),
                $conditions->getEnd(),
                $conditions->getAdditionalBegin(),
                $conditions->getAdditionalEnd(),
                $tariffIds,
                $roomTypeIds
            );


        $tariffRoomTypeCombined = $this->combineTariffWithRoomType($roomTypeIds, $tariffIds);
        $combinations = $this->combineDataForSearchQuery($dates, $tariffRoomTypeCombined);
        if (empty($combinations)) {
            throw new SearchQueryGeneratorException('No combinations for search');
        }

        return $combinations;
    }

    private function combineDataForSearchQuery(array $dates, array $tariffRoomTypeCombined): array
    {
        $result = [];
        foreach ($dates as $date) {
            foreach ($tariffRoomTypeCombined as $tariffRoomType) {
                $result[] = array_merge($date, $tariffRoomType);
            }
        }

        return $result;
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
     * @throws SearchQueryGeneratorException
     */
    private function getRoomTypeIds(ArrayCollection $roomTypes, array $hotelIds): array
    {
        $roomTypeIds = [];
        if ($roomTypes->count()) {
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[$roomType->getHotel()->getId()][] = $roomType->getId();
            }

            return $roomTypeIds;
        }

        $roomTypesRaw = $this->dm->getRepository(RoomType::class)->fetchRaw($roomTypeIds, $hotelIds);
        if (empty($roomTypesRaw)) {
            throw new SearchQueryGeneratorException(
                'Empty Result in RoomType query, cannot create SearchQuery without any RoomType'
            );
        }

        foreach ($roomTypesRaw as $roomTypeId => $roomType) {
            $roomTypeIds[(string)$roomType['hotel']['$id']][] = $roomTypeId;
        }

        return $roomTypeIds;
    }


    /**
     * @param array $roomTypeIds
     * @param array $tariffIds
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function combineTariffWithRoomType(array $roomTypeIds, array $tariffIds): array
    {
        $roomTypeHotelKeys = array_keys($roomTypeIds);
        $tariffHotelKeys = array_keys($tariffIds);
        $sharedHotelKeys = array_intersect($roomTypeHotelKeys, $tariffHotelKeys);
        if (empty($sharedHotelKeys)) {
            throw new SearchQueryGeneratorException('There is an error in combine Tariff with RoomType');
        }
        $combined = [];
        foreach ($sharedHotelKeys as $hotelKey) {
            $roomTypes = $roomTypeIds[$hotelKey];
            $tariffs = $tariffIds[$hotelKey];
            /** https://stackoverflow.com/questions/23348339/optimizing-array-merge-operation
             * Potential performance problem if use array_merge in loop.
             */
            $combined[] = $this->mixRoomTypeTariff($roomTypes, $tariffs);
        }

        $result = [];
        foreach ($combined as $values) {
            /** @var array $values */
            if (is_iterable($values)) {
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    private function mixRoomTypeTariff(array $roomTypes, array $tariffs)
    {
        $values = [];
        foreach ($roomTypes as $roomType) {
            foreach ($tariffs as $tariff) {
                $values[] = ['roomType' => $roomType, 'tariff' => $tariff];
            }
        }

        return $values;
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