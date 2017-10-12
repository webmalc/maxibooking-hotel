<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorRoomType;
use MBH\Bundle\HotelBundle\Document\Hotel;
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

    public function getAvailabilityData($startDate, $endDate, $tripAdvisorConfigs)
    {
        $availabilityData = [];
        /** @var TripAdvisorConfig $tripAdvisorConfig */
        foreach ($tripAdvisorConfigs as $tripAdvisorConfig) {
            $searchResult = $this->search($startDate, $endDate, $tripAdvisorConfig->getHotel());
            foreach ($searchResult as $result) {
                $availabilityData[$tripAdvisorConfig->getHotel()->getId()][] = $result;
            }
        }

        return $availabilityData;
    }

    public function getTripAdvisorConfigs($hotelIds)
    {
        $tripAdvisorConfigRepository = $this->dm->getRepository('MBHChannelManagerBundle:TripAdvisorConfig');

        $configs = $tripAdvisorConfigRepository->createQueryBuilder()
            ->field('hotel.id')->in($hotelIds)->getQuery()->execute();

        $result = [];
        foreach ($configs as $config) {
            /** @var TripAdvisorConfig $config */
            $result[$config->getHotel()->getId()] = $config;
        }

        return $result;
    }

    public function getBookingOptionsByHotel($startDate, $endDate, Hotel $hotel)
    {
        return $this->search($startDate, $endDate, $hotel);
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
            $availableRoomTypeIds = [];
            foreach ($requestedHotel->getTripAdvisorConfig()->getRooms() as $tripAdvisorRoomType) {
                /** @var TripAdvisorRoomType $tripAdvisorRoomType */
                if ($tripAdvisorRoomType->getIsEnabled()) {
                    $availableRoomTypeIds[] = $tripAdvisorRoomType->getRoomType()->getId();
                }
            }

            $this->availableRoomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')
                ->fetch($requestedHotel, $availableRoomTypeIds);
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

    private function search(\DateTime $startDate, \DateTime $endDate, Hotel $hotel, Tariff $tariff = null)
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = $startDate;
        $query->end = $endDate;
        $query->adults = 0;
        $query->children = 0;

        /** @var TripAdvisorRoomType $room */
        foreach ($hotel->getTripAdvisorConfig()->getRooms() as $room) {
            if ($room->getIsEnabled()) {
                $query->addRoomType($room->getRoomType()->getId());
            } else {
                $query->addExcludeRoomType([$room->getRoomType()->getId()]);
            }
        }

        if (is_null($tariff)) {
            $this->search->setWithTariffs();
        } else {
            $query->tariff = $tariff;
        }

        return $this->search->search($query);
    }
}