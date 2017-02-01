<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

class TripAdvisorPackageInfo extends AbstractPackageInfo
{
    private $isCorrupted;
    private $roomData;
    private $checkInDate;
    private $checkOutDate;
    private $bookingMainData;
    private $bookingSessionId;
    /** @var Helper $helper */
    private $helper;

    private $isPricesInit = false;
    private $prices = [];

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

        return $this;
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
        $roomTypeId = $this->bookingMainData['roomTypeId'];
        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
        if (!$roomType) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $this->bookingMainData['hotelId'],
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $this->addProblemMessage('services.expedia.invalid_room_type_id');
            $this->isCorrupted = true;
        }

        if (!$roomType) {
            throw new \ChannelManagerException($this->translator->trans('services.expedia.nor_one_room_type'));
        }

        return $roomType;
    }

    public function getTariff()
    {
        return $this->dm->find('MBHPriceBundle:Tariff', $this->bookingMainData['tariffId']);
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
        if (!$this->isPricesInit) {
            $childrenAdultsString = $this->getAdultsCount() . '_' . $this->getChildrenCount();
            $pricesByDate = $this->bookingMainData['pricesByDate'][$childrenAdultsString];
            foreach ($pricesByDate as $dateString => $priceByDate) {
                $currentDate = \DateTime::createFromFormat('d_m_Y', $dateString);
                $this->prices[] = new PackagePrice($currentDate, $priceByDate, $this->getTariff());
            }
            $this->isPricesInit = true;
        }

        return $this->prices;
    }

    public function getPrice()
    {
        $price = 0;
        foreach ($this->getPrices() as $priceDocument) {
            /** @var PackagePrice $priceDocument */
            $price += $priceDocument->getPrice();
        }

        return $price;
    }

    //TODO: Убрать
    public function getNote()
    {
        return $this->note;
    }

    private function getChildrenData()
    {
        $childrenData = $this->roomData['party']['children'];
        if (count($childrenData) > 0) {
            $childrenAgesString = join(', ', $childrenData);
            $this->addNotifyMessage( 'package_info.tripadvisor.children_age', $childrenAgesString);
        }

        return $this->roomData['party']['children'];
    }

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
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