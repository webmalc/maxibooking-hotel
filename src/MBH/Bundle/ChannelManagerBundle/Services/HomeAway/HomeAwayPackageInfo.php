<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;

class HomeAwayPackageInfo extends AbstractPackageInfo
{
    /** @var \SimpleXMLElement $bookingRequest */
    private $bookingRequest;
    /** @var  HomeAwayConfig $config */
    private $config;
    private $price;
    private $tourists;

    /**
     * @param \SimpleXMLElement $bookingRequest
     * @param HomeAwayConfig $config
     * @return HomeAwayPackageInfo
     */
    public function setInitData(\SimpleXMLElement $bookingRequest, HomeAwayConfig $config) : HomeAwayPackageInfo
    {
        $this->bookingRequest = $bookingRequest;
        $this->config = $config;

        return $this;
    }

    public function getBeginDate() : \DateTime
    {
        $reservationDates = $this->getReservationData()->reservationDates;
        $beginDateString = trim((string)$reservationDates->beginDate);

        return Helper::getDateFromString('Y-m-d', $beginDateString);
    }

    public function getEndDate() : \DateTime
    {
        $reservationDates = $this->getReservationData()->reservationDates;
        $endDateString = trim((string)$reservationDates->endDate);

        return Helper::getDateFromString('Y-m-d', $endDateString);
    }

    public function getRoomType()
    {
        $roomTypeId = trim((string)$this->bookingRequest->listingExternalId);
        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);

        if (is_null($roomType)) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $this->config->getHotelId(),
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

    public function getChannelManagerId()
    {
        return trim((string)$this->bookingRequest->inquiryId);
    }

    private function getPaymentData()
    {
        return $this->bookingRequest->paymentForm;
    }

    private function getReservationData() : \SimpleXMLElement
    {
        return $this->bookingRequest->reservation[0];
    }
}