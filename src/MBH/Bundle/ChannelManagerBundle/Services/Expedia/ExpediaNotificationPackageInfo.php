<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;

class ExpediaNotificationPackageInfo extends AbstractPackageInfo
{
    /** @var ExpediaConfig $config */
    private $config;
    private $packageDataXMLElement;
    private $roomTypes;
    private $tariffs;
    private $payer;
    private $isSmoking = false;
    private $channelManagerId;
    private $isCorrupted = false;

    private $isRoomTypeInit = false;
    private $roomType;
    private $isTariffInit = false;
    private $tariff;

    /**
     * @param $packageData
     * @param $config
     * @param $tariffs
     * @param $roomTypes
     * @param $payer
     * @return ExpediaNotificationPackageInfo
     */
    public function setInitData($packageData, $config, $tariffs, $roomTypes, $payer)
    {
        $this->payer = $payer;
        $this->config = $config;
        $this->packageDataXMLElement = $packageData;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;

        return $this;
    }

    public function getBeginDate()
    {
        // TODO: Implement getBeginDate() method.
    }

    public function getEndDate()
    {
        // TODO: Implement getEndDate() method.
    }

    public function getRoomType()
    {
        if (!$this->isRoomTypeInit) {
            $roomTypeId = $this->packageDataXMLElement->RoomTypes->RoomType->attributes()['RoomTypeCode'];
            if (isset($this->roomTypes[$roomTypeId])) {
                $this->roomType = $this->roomTypes[$roomTypeId]['doc'];
            } else {
                $this->roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                    [
                        'hotel.id' => $this->config->getHotel()->getId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $this->addPackageNote($this->translator->trans('services.expedia.invalid_room_type_id'));
                $this->isCorrupted = true;
            }
            if (!$this->roomType) {
                throw new \Exception($this->translator->trans('services.expedia.nor_one_room_type'));
            }
            $this->isRoomTypeInit = true;
        }

        return $this->roomType;
    }

    /**
     * @return \MBH\Bundle\PriceBundle\Document\Tariff|null|object
     * @throws \Exception
     */
    public function getTariff()
    {
        if (!$this->isTariffInit) {
            /** @var \SimpleXMLElement $firstRatePlansElement */
            $firstRatePlansElement = $this->packageDataXMLElement->RatePlans->RatePlan[0];
            $serviceTariffId = $firstRatePlansElement->attributes()['RatePlanCode'];
            if (isset($this->tariffs[$serviceTariffId])) {
                $this->tariff = $this->tariffs[$serviceTariffId]['doc'];
            } else {
                $this->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
                    [
                        'hotel.id' => $this->config->getHotel()->getId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $this->addPackageNote($this->translator->trans('services.expedia.invalid_tariff_id'));
                $this->isCorrupted = true;
            }

            if (!isset($this->tariff)) {
                throw new \Exception($this->translator->trans('services.expedia.nor_one_tariff'));
            }
            $this->isTariffInit = true;
        }

        return $this->tariff;
    }

    public function getAdultsCount()
    {
        // TODO: Implement getAdultsCount() method.
    }

    public function getChildrenCount()
    {
        // TODO: Implement getChildrenCount() method.
    }

    public function getPrices()
    {
        // TODO: Implement getPrices() method.
    }

    public function getPrice()
    {
        // TODO: Implement getPrice() method.
    }

    public function getNote()
    {
        // TODO: Implement getNote() method.
    }

    public function getIsCorrupted()
    {
        // TODO: Implement getIsCorrupted() method.
    }

    public function getTourists()
    {
        return [$this->payer];
    }

    public function getIsSmoking()
    {
        return $this->isSmoking;
    }

    /**
     * @param $isSmoking
     * @return ExpediaNotificationPackageInfo
     */
    public function setIsSmoking($isSmoking)
    {
        $this->isSmoking = $isSmoking;

        return $this;
    }

    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }

    /**
     * @param $channelManagerId
     * @return ExpediaNotificationPackageInfo
     */
    public function setChannelManagerId($channelManagerId)
    {
        $this->channelManagerId = $channelManagerId;

        return $this;
    }
}