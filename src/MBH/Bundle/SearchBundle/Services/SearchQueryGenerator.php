<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQueryHelper;
use MBH\Bundle\SearchBundle\Lib\HotelContentHolder;

class SearchQueryGenerator
{
    /** @var DocumentManager */
    private $dm;

    /** @var AdditionalDatesGenerator */
    private $addDatesGenerator;

    /** @var RoomTypeFetcher */
    private $roomTypeFetcher;

    /** @var HotelContentHolder */
    private $hotelContentHolder;

    public function __construct(DocumentManager $dm, AdditionalDatesGenerator $generator, RoomTypeFetcher $roomTypeFetcher, HotelContentHolder $contentHolder)
    {
        $this->dm = $dm;
        $this->addDatesGenerator = $generator;
        $this->roomTypeFetcher = $roomTypeFetcher;
        $this->hotelContentHolder = $contentHolder;
    }

    /**
     * @param SearchConditions $conditions
     * @return SearchQuery[]
     * @throws SearchQueryGeneratorException
     */
    public function generateSearchQueries(SearchConditions $conditions): array
    {
        $queryHelpers = $this->prepareConditionsForSearchQueries($conditions);
        $searchQueries = [];
        foreach ($queryHelpers as $queryHelper) {
            $searchQueries[] = SearchQuery::createInstance($queryHelper, $conditions);
        }

        return $searchQueries;
    }


    /**
     * @param SearchConditions $conditions
     * @return SearchQueryHelper[]
     * @throws SearchQueryGeneratorException
     */
    private function prepareConditionsForSearchQueries(SearchConditions $conditions): array
    {
        $hotelIds = $this->getEntryIds($conditions->getHotels());

        $rawTariffIds = $this->getEntryIds($conditions->getTariffs()->toArray());
        $tariffs = $this->getTariffs($rawTariffIds, $hotelIds, $conditions->isOnline());
        $rawRoomTypeIds = $this->getEntryIds($conditions->getRoomTypes());
        $roomTypeIds = $this->getRoomTypeIds($rawRoomTypeIds, $hotelIds);

        $dates =
            $this->addDatesGenerator->generate(
                $conditions->getBegin(),
                $conditions->getEnd(),
                $conditions->getAdditionalBegin(),
                $conditions->getAdditionalEnd(),
                $tariffs,
                $roomTypeIds
            );
        $tariffRoomTypeCombined = $this->combineTariffWithRoomType($roomTypeIds, $tariffs);

        $queryHelpers = $this->combineDataForSearchQuery($dates, $tariffRoomTypeCombined);
        if (empty($queryHelpers)) {
            throw new SearchQueryGeneratorException('No combinations for search');
        }

        return $queryHelpers;
    }


    /**
     * @param ArrayCollection|Tariff[] $tariffIds
     * @param array $hotelIds
     * @param bool $isOnline
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    private function getTariffs(array $tariffIds, array $hotelIds, bool $isOnline): array
    {
        $tariffs = [];
        /** Priority to Tariff even if hotels exists */
        if (\count($tariffIds)) {
            $hotelIds = [];
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
            $tariffs[(string)$tariff['hotel']['$id']][] = [
                'id' => $tariffId,
                'rawTariff' => $tariff
            ];
        }

        return $tariffs;
    }

    /**
     * @param iterable $rawRoomTypeIds
     * @param array $hotelIds
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function getRoomTypeIds(iterable $rawRoomTypeIds, array $hotelIds): array
    {
        $roomTypeIds = [];
        /** Prior to roomType even hotel is defined */
        if (\count($rawRoomTypeIds)) {
            $hotelIds = [];
        }

        $roomTypesRaw = $this->roomTypeFetcher->fetch($rawRoomTypeIds, $hotelIds);
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
     * @param array $dates
     * @param array $tariffRoomTypeCombined
     * @return SearchQueryHelper[]
     */
    private function combineDataForSearchQuery(array $dates, array $tariffRoomTypeCombined): array
    {
        $result = [];
        foreach ($dates as $date) {
            foreach ($tariffRoomTypeCombined as $tariffRoomType) {
                $result[] = SearchQueryHelper::createInstance($date, $tariffRoomType);
            }
        }

        return $result;
    }


    private function getEntryIds(iterable $entry): array
    {
        return Helper::toIds($entry);
    }


    /**
     * @param array $rawRoomTypeIds
     * @param array $rawTariffs
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function combineTariffWithRoomType(array $rawRoomTypeIds, array $rawTariffs): array
    {
        $roomTypeHotelKeys = array_keys($rawRoomTypeIds);
        $tariffHotelKeys = array_keys($rawTariffs);
        $sharedHotelKeys = array_intersect($roomTypeHotelKeys, $tariffHotelKeys);
        if (empty($sharedHotelKeys)) {
            throw new SearchQueryGeneratorException('There is an error in combine Tariff with RoomType');
        }
        $combined = [];
        foreach ($sharedHotelKeys as $hotelKey) {
            $roomTypeIds = $rawRoomTypeIds[$hotelKey];
            $tariffId = $rawTariffs[$hotelKey];
            /** https://stackoverflow.com/questions/23348339/optimizing-array-merge-operation
             * Potential performance problem if use array_merge in loop.
             */
            $combined[] = $this->mixRoomTypeTariff($roomTypeIds, $tariffId);
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


    private function mixRoomTypeTariff(array $roomTypes, array $tariffs): array
    {
        $values = [];
        foreach ($roomTypes as $roomType) {
            foreach ($tariffs as $tariff) {
                $values[] = [
                    'roomTypeId' => $roomType,
                    'tariffId' => $tariff['id'],
                    'tariff' => $tariff['rawTariff'],
                    'restrictionTariffId' => $this->determineRestrictionTariffId($tariff['rawTariff'])

                ];
            }
        }

        return $values;
    }

    private function determineRestrictionTariffId(array $rawTariff): string
    {
        $tariff = $this->hotelContentHolder->getFetchedTariff((string)$rawTariff['_id']);
        if ($tariff) {
            if ($tariff->getParent() && $tariff->getChildOptions() && $tariff->getChildOptions()->isInheritRestrictions()) {
                $restrictionTariffId = $tariff->getParent()->getId();
            } else {
                $restrictionTariffId = $tariff->getId();
            }

            return $restrictionTariffId;
        }

        throw new SearchQueryGeneratorException('No tariff for restriction');
    }

}