<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class TripAdvisorDataFormatter
{
    /** @var  SearchFactory $search */
    private $search;
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(SearchFactory $search, DocumentManager $dm)
    {
        $this->search = $search;
        $this->dm = $dm;
    }

    public function getAvailabilityData($startDate, $endDate, $adultsAndChildrenData, $hotelsSyncData)
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = \DateTime::createFromFormat('Y-m-d' . ' H:i:s', $startDate . ' 00:00:00');
        $query->end = \DateTime::createFromFormat('Y-m-d' . ' H:i:s', $endDate . ' 00:00:00');
//        $query->tariff = $config->getTariff();
        $query->tariff = "5864fc912f77d901104b5794";

        $availabilityData = [];

        foreach ($hotelsSyncData as $hotelSyncData) {
            $mbhHotelId = $hotelSyncData['partner_id'];
            $requestedHotel = $this->dm->find('MBHHotelBundle:Hotel', $mbhHotelId);
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