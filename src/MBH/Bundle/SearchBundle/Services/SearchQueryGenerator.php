<?php


namespace MBH\Bundle\SearchBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\MongoDBException;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException;
use MBH\Bundle\SearchBundle\Lib\Result\GroupSearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQuery;
use MBH\Bundle\SearchBundle\Lib\SearchQueryHelper;
use MBH\Bundle\SearchBundle\Lib\DataHolder;

class SearchQueryGenerator
{
    /** @var AdditionalDatesGenerator */
    private $addDatesGenerator;

    /** @var DataHolder */
    private $dataHolder;

    public function __construct(AdditionalDatesGenerator $generator, DataHolder $contentHolder)
    {
        $this->addDatesGenerator = $generator;
        $this->dataHolder = $contentHolder;
    }

    /**
     * @param SearchConditions $conditions
     * @param bool $grouped
     * @return SearchQuery[]|GroupSearchQuery[]
     * @throws SearchQueryGeneratorException
     */
    public function generate(SearchConditions $conditions, bool $grouped = false): array
    {
        $dates = $this->addDatesGenerator->generate(
            $conditions->getBegin(),
            $conditions->getEnd(),
            $conditions->getAdditionalBegin(),
            $conditions->getAdditionalEnd()
        );

        $tariffRoomTypeCombinations = $this->getTariffRoomTypesCombinations($conditions);

        $result = [];
        foreach ($dates as $period) {
            $begin = $period['begin'];
            $end = $period['end'];
            if ($grouped) {
                $queryGroup = new GroupSearchQuery();
                /** @noinspection TypeUnsafeComparisonInspection */
                $isMainGroup = ($begin == $conditions->getBegin()) && ($end == $conditions->getEnd());
                $queryGroup->setBegin($begin)->setEnd($end)->setType($isMainGroup ? GroupSearchQuery::MAIN_DATES : GroupSearchQuery::ADDITIONAL_DATES);
                foreach ($tariffRoomTypeCombinations as $combination) {
                    $queryGroup->addSearchQuery(SearchQuery::createInstance($conditions, $begin, $end, $combination));
                }
                $result[] = $queryGroup;
            } else {
                foreach ($tariffRoomTypeCombinations as $combination) {
                    $result [] = SearchQuery::createInstance($conditions, $begin, $end, $combination);
                }
            }
        }

        return $result;
    }


    /**
     * @param SearchConditions $conditions
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function getTariffRoomTypesCombinations(SearchConditions $conditions): array
    {
        $hotelIds = $this->getEntryIds($conditions->getHotels());

        $tariffIds = $this->getEntryIds($conditions->getTariffs()->toArray());
        $tariffsGroupedByHotelId = $this->getTariffs($tariffIds, $hotelIds, $conditions->isOnline());

        $roomTypeIds = $this->getEntryIds($conditions->getRoomTypes());
        $roomTypeIdsGroupedByHotel = $this->getRoomTypeIds($roomTypeIds, $hotelIds);

        return $this->combineTariffWithRoomType($roomTypeIdsGroupedByHotel, $tariffsGroupedByHotelId);
    }


    /**
     * @param array $roomTypeGroupedByHotelId
     * @param array $tariffsGroupedByHotelId
     * @return array
     * @throws SearchQueryGeneratorException
     */
    private function combineTariffWithRoomType(
        array $roomTypeGroupedByHotelId,
        array $tariffsGroupedByHotelId
    ): array {
        $roomTypeHotelIdsKeys = array_keys($roomTypeGroupedByHotelId);
        $tariffHotelIdsKeys = array_keys($tariffsGroupedByHotelId);
        $sharedHotelKeys = array_intersect($roomTypeHotelIdsKeys, $tariffHotelIdsKeys);
        if (empty($sharedHotelKeys)) {
            throw new SearchQueryGeneratorException('There is an error in combine Tariff with RoomType');
        }
        $combined = [];
        foreach ($sharedHotelKeys as $hotelKey) {
            $roomTypes = $roomTypeGroupedByHotelId[$hotelKey];
            $tariffs = $tariffsGroupedByHotelId[$hotelKey];
            /** https://stackoverflow.com/questions/23348339/optimizing-array-merge-operation
             * Potential performance problem if use array_merge in loop.
             */
            $combined[] = $this->mixRoomTypeTariff($roomTypes, $tariffs);
        }

        $result = [];
        if (\count($combined)) {
            $result = array_merge(...$combined);
        }

        return $result;
    }


    /**
     * @param ArrayCollection|Tariff[] $rawTariffIds
     * @param array $hotelIds
     * @param bool $isOnline
     * @return array
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     */
    private function getTariffs(array $rawTariffIds, array $hotelIds, bool $isOnline): array
    {
        $tariffs = [];
        /** Priority to Tariff even if hotels exists */
        if (\count($rawTariffIds)) {
            $hotelIds = [];
        }
        try {
            $tariffsRaw = $this->dataHolder->getTariffsRaw($hotelIds, $rawTariffIds, true, $isOnline);
        } catch (MongoDBException $e) {
            throw new SearchQueryGeneratorException('Error in fetchRaw repo method. ' . $e->getMessage());
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

        $roomTypesRaw = $this->dataHolder->getRoomTypesRaw($rawRoomTypeIds, $hotelIds);
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


    private function getEntryIds(iterable $entry): array
    {
        return Helper::toIds($entry);
    }


    private function mixRoomTypeTariff(array $roomTypes, array $tariffs): array
    {
        //** TODO: Костыль. Тут rawTariff передается чтоб забрать из него настройки возрастов в тарифе
        // т.к. у нас появляются merged тарифы, настройки будем брать из них. значит это убирать
        // так же остается вопрос с restrictionTariffId ибо нужно будет брать restrictions взависимости
        // от того какой тариф мы получаем потом в mergedPriceCache
        //
        // */

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
        $tariff = $this->dataHolder->getFetchedTariff((string)$rawTariff['_id']);
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