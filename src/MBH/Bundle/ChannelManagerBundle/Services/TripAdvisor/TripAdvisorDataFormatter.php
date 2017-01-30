<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
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


    public function getTripAdvisorConfigs($tripAdvisorHotelIds = null)
    {
        $tripAdvisorConfigRepository = $this->dm->getRepository('MBHChannelManagerBundle:TripAdvisorConfig');
        if ($tripAdvisorHotelIds) {
            return $tripAdvisorConfigRepository->findAll();
        }

        return $tripAdvisorConfigRepository->createQueryBuilder()
            ->field('hotelId')->in($tripAdvisorHotelIds);
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

    public function getOrderById($orderId, $isWithDeleted = false)
    {
        if ($isWithDeleted) {
            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }
        }

        $order = $this->dm->find('MBHPackageBundle:Order', $orderId);

        if ($isWithDeleted) {
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }
        }

        return $order;
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

    public function getBookingSyncData($syncOrderData)
    {
        $syncOrders = [];
        foreach ($syncOrderData as $orderData) {
            $orderId = $orderData['reservation_id'];
            $hotelId = $orderData['partner_hotel_code'];
            $order = $this->getOrderById($orderId, true);

            if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }

            $packages = $order->getPackages();

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $syncOrders[] = [
                'orderId' => $orderId,
                'hotelId' => $hotelId,
                'order' => $order,
                'packages' => $packages
            ];
        }

        return $syncOrders;
    }

    private function getTripAdvisorHotelId($mbhHotelId, $hotelIdsSyncData)
    {
        foreach ($hotelIdsSyncData as $syncData) {
            if ($syncData['partner_id'] == $mbhHotelId) {
                return $syncData['ta_id'];
            }
        }
        //TODO: Какую ошибку?
        throw new \Exception();
    }
}