<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TripAdvisorDataFormatter
{
    /** @var  SearchFactory $search */
    private $search;
    /** @var  DocumentManager $dm */
    private $dm;

    private $isAvailableRoomTypesInit = false;
    private $availableRoomTypes;
    private $isAvailableTariffsInit = false;
    private $availableTariffs;

    public function __construct(SearchFactory $search, DocumentManager $dm)
    {
        $this->search = $search;
        $this->dm = $dm;
    }

    public function getAvailabilityData($startDate, $endDate, $hotelsSyncData)
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = \DateTime::createFromFormat('Y-m-d', $startDate);
        $query->end = \DateTime::createFromFormat('Y-m-d', $endDate);
//        $query->tariff = $config->getTariff();
//        if ($tariff) {
//            $query->tariff = $tariff->getId();
//        }
        //TODO: Уточнить насчет тарифа
        $query->tariff = "5864fc912f77d901104b5794";

        $availabilityData = [];

        foreach ($hotelsSyncData as $hotelSyncData) {
            $mbhHotelId = $hotelSyncData['partner_id'];
            $requestedHotel = $this->getHotelById($mbhHotelId);
            if (is_null($requestedHotel)) {
                //TODO: Что делать с исключением?
                throw new \Exception();
            }
            $query->addHotel($requestedHotel);
        }

        $searchResult = $this->search->setWithTariffs()->search($query);

        foreach ($searchResult as $result) {
            /** @var RoomType $roomType */
            $roomType = $result['roomType'];
            $mbhHotelId = $roomType->getHotel()->getId();
            $tripAdvisorHotelId = $this->getTripAdvisorHotelId($mbhHotelId, $hotelsSyncData);
            $availabilityData[$tripAdvisorHotelId][] = $result;
        }

        return $availabilityData;
    }

    public function getSearchResults($startDate, $endDate, Hotel $hotel)
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = \DateTime::createFromFormat('Y-m-d', $startDate);
        $query->end = \DateTime::createFromFormat('Y-m-d', $endDate);
        $query->addHotel($hotel);

        $searchResult = $this->search->setWithTariffs()->search($query);

        return $searchResult;
    }

    public function getHotelById($hotelId)
    {
        return $this->dm->find('MBHHotelBundle:Hotel', $hotelId);
    }

    public function getAvailableRoomTypes(Hotel $requestedHotel)
    {
        if (!$this->isAvailableRoomTypesInit) {

            $this->availableRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->fetch($requestedHotel);
            $this->isAvailableRoomTypesInit = true;
        }

        return $this->availableRoomTypes;
    }


    public function getAvailableTariffs(Hotel $requestedHotel, \DateTime $begin, \DateTime $end)
    {
        if (!$this->isAvailableTariffsInit) {

            $this->availableTariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
                ->getTariffsByDates($requestedHotel, $begin, $end);
            $this->isAvailableTariffsInit = true;
        }

        return $this->availableTariffs;
    }

    private function getTripAdvisorHotelId($mbhHotelId, $hotelIdsSyncData) {
        foreach ($hotelIdsSyncData as $syncData) {
            if ($syncData['partner_id'] == $mbhHotelId) {
                return $syncData['ta_id'];
            }
        }
        //TODO: Какую ошибку?
        throw new \Exception();
    }
}