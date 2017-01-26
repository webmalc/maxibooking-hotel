<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;

class TripAdvisorPackageInfo extends AbstractPackageInfo
{
    private $roomData;
    private $checkInDate;
    private $checkOutDate;
    private $bookingMainData;
    private $bookingSessionId;
    /** @var Helper $helper */
    private $helper;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->helper = $this->container->get('mbh.helper');
    }

    public function setInitData($roomData, $checkInDate, $checkOutDate, $bookingMainData, $bookingSessionId)
    {
        $this->roomData = $roomData;
        $this->checkOutDate = $checkOutDate;
        $this->checkInDate = $checkInDate;
        $this->bookingMainData = $bookingMainData;
        $this->bookingSessionId = $bookingSessionId;
    }

    public function getBeginDate()
    {
        return $this->helper->getDateFromString($this->checkInDate, 'Y-m-d');
    }

    public function getEndDate()
    {
        return $this->helper->getDateFromString($this->checkOutDate, 'Y-m-d');
    }

    public function getRoomType()
    {
        $roomTypeId = $this->getPackageCommonData('roomTypeID');
        if (isset($this->roomTypes[$roomTypeId])) {
            $roomType = $this->roomTypes[$roomTypeId]['doc'];
        } else {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $this->config->getHotelId(),
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $this->addPackageNote($this->translator->trans('services.expedia.invalid_room_type_id'));
            $this->isCorrupted = true;
        }
        if (!$roomType) {
            throw new \Exception($this->translator->trans('services.expedia.nor_one_room_type'));
        }

        return $roomType;
    }

    public function getTariff()
    {
        // TODO: Implement getTariff() method.
    }

    public function getAdultsCount()
    {
        return $this->roomData['party']['adults'];
    }

    public function getChildrenCount()
    {
        return count($this->getChildrenData());
    }

    public function getPrices()
    {
        $query = new SearchQuery();

        $query->accommodations = true;
        $query->begin = $this->getBeginDate();
        $query->end = $this->getEndDate();
        $query->adults = $this->getAdultsCount();
        $query->setChildrenAges($this->getChildrenData());
        $query->addHotel($hotel);

        $searchResult = $this->container->get('mbh.package.search')->setWithTariffs()->search($query);
    }

    public function getPrice()
    {
        // TODO: Implement getPrice() method.
    }

    public function getNote()
    {
        $childrenData = $this->getChildrenData();
        if (count($childrenData) > 0) {
            $childrenAgesString = join(', ', $childrenData);
            $this->addPackageNote($childrenAgesString, 'package_info.tripadvisor.children_age');
        }
        return $this->note;
    }

    private function getChildrenData()
    {
        return $this->roomData['party']['children'];
    }

    public function getIsCorrupted()
    {
        // TODO: Implement getIsCorrupted() method.
    }

    public function getTourists()
    {
        $firstName = $this->roomData['traveler_first_name'];
        $lastName = $this->roomData['traveler_last_name'];
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            $lastName,
            $firstName
        );

        return [$payer];
    }

    public function getIsSmoking()
    {
        return false;
    }

    public function getChannelManagerId()
    {
        return $this->bookingSessionId;
    }
}