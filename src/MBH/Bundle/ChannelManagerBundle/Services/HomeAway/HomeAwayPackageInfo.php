<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;

class HomeAwayPackageInfo extends AbstractPackageInfo
{
    /** @var \SimpleXMLElement $bookingRequest */
    private $bookingRequest;
    /** @var  HomeAwayConfig $config */
    private $config;
    private $price;
    private $tourists;
    private $channelManagerId;

    /**
     * @param \SimpleXMLElement $bookingRequest
     * @return HomeAwayPackageInfo
     */
    public function setInitData(\SimpleXMLElement $bookingRequest) : HomeAwayPackageInfo
    {
        $this->bookingRequest = $bookingRequest;
        $this->config = $this->getRoomType()->getHotel()->getHomeAwayConfig();

        return $this;
    }

    public function getBeginDate() : \DateTime
    {
        $reservationDates = $this->getReservationData()->reservationDates;
        $beginDateString = trim((string)$reservationDates->beginDate);

        return Helper::getDateFromString($beginDateString, 'Y-m-d');
    }

    public function getEndDate() : \DateTime
    {
        $reservationDates = $this->getReservationData()->reservationDates;
        $endDateString = trim((string)$reservationDates->endDate);

        return Helper::getDateFromString($endDateString, 'Y-m-d');
    }

    public function getRoomType() : RoomType
    {
        $roomTypeId = trim((string)$this->bookingRequest->listingExternalId);
        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);

        if (is_null($roomType)) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $this->addPackageNote($this->translator->trans('services.home_away.invalid_room_type_id'));
            $this->isCorrupted = true;
        }
        if (!$roomType) {
            throw new \Exception($this->translator->trans('services.home_away.nor_one_room_type'));
        }

        return $roomType;
    }

    public function getTariff()
    {
        return $this->config->getMainTariff();
    }

    public function getAdultsCount()
    {
        return (int)$this->getReservationData()->numberOfAdults;
    }

    public function getChildrenCount()
    {
        return (int)$this->getReservationData()->numberOfChildren;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrices()
    {
        return [];
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
    }

    public function getTourists()
    {
        return $this->tourists;
    }

    public function setTourists($tourists)
    {
        $this->tourists = $tourists;

        return $this;
    }

    public function getIsSmoking()
    {
        return false;
    }

    public function setChannelManagerId($id)
    {
        $this->channelManagerId = $id;

        return $this;
    }

    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }

    private function getReservationData() : \SimpleXMLElement
    {
        return $this->bookingRequest->reservation[0];
    }
}