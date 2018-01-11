<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;

use MBH\Bundle\PackageBundle\Document\PackagePrice;

class ExpediaPackageInfo extends AbstractPackageInfo
{
    /** @var ExpediaConfig $config */
    private $config;
    /**
     * Данные о брони, в виде объекта SimpleXMLElement
     * @var \SimpleXMLElement $orderDataXMLElement
     */
    private $packageDataXMLElement;
    private $roomTypes;
    private $tariffs;
    private $payer;
    private $isCorrupted = false;
    private $isPricesInit = false;
    private $prices = [];
    private $isSmoking = false;
    private $channelManagerId;
    private $isTariffInit = false;
    private $tariff;
    private $isRoomTypeInit = false;
    private $roomType;

    /**
     * @param $packageData
     * @param $config
     * @param $tariffs
     * @param $roomTypes
     * @param $payer
     * @return ExpediaPackageInfo
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

    public function getTariffId()
    {
        return (int)$this->getPackageCommonData('ratePlanID');
    }

    private function getPackageCommonData($param)
    {
        return (string)$this->packageDataXMLElement->attributes()[$param];
    }

    /**
     * Ленивая загрузка метода получения массива PackagePrice.
     * @return PackagePrice[]
     */
    public function getPrices() {
        if (!$this->isPricesInit) {
            foreach ($this->packageDataXMLElement->PerDayRates->PerDayRate as $perDayRate) {
                /** @var \SimpleXMLElement $perDayRate */
                $currentDate = \DateTime::createFromFormat('Y-m-d', $perDayRate->attributes()['stayDate']);
                $baseRate = (float)$perDayRate->attributes()['baseRate'];
                $extraPersonFee = isset($perDayRate->attributes()['extraPersonFees'])
                    ? (float)$perDayRate->attributes()['extraPersonFees']
                    : 0;
                $hotelServicesFees = isset($perDayRate->attributes()['hotelServiceFees'])
                    ? (float)$perDayRate->attributes()['hotelServiceFees']
                    : 0;

                $price = $baseRate + $extraPersonFee + $hotelServicesFees;
                $this->prices[] = new PackagePrice($currentDate, $price, $this->getTariff());
            }
            $this->isPricesInit = true;
        }

        return $this->prices;
    }

    public function getRoomType()
    {
        if (!$this->isRoomTypeInit) {

            $roomTypeId = $this->getPackageCommonData('roomTypeID');
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

    public function getTariff()
    {
        if (!$this->isTariffInit) {
            $serviceTariffId = $this->getPackageCommonData('ratePlanID');
            if (isset($this->tariffs[$serviceTariffId])) {
                $this->tariff = $this->tariffs[$serviceTariffId]['doc'];
            } elseif (isset($this->tariffs[$serviceTariffId . 'A'])) {
                $this->tariff = $this->tariffs[$serviceTariffId . 'A']['doc'];
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

    public function getBeginDate()
    {
        $prices = $this->getPrices();
        /** @var PackagePrice $firstPackagePrice */
        $firstPackagePrice = current($prices);

        return $firstPackagePrice->getDate();
    }

    public function getEndDate()
    {
        $prices = $this->getPrices();
        /** @var PackagePrice $lastPackagePrice */
        $lastPackagePriceDate = clone (end($prices)->getDate());

        return ($lastPackagePriceDate->add(new \DateInterval('P1D')));
    }

    public function getAdultsCount()
    {
        return (int)$this->packageDataXMLElement->GuestCount->attributes()['adult'];
    }

    public function getChildrenCount()
    {
        return (int)$this->packageDataXMLElement->GuestCount->attributes()['child'];
    }

    public function getPrice()
    {
        $totalPrice = 0;
        foreach ($this->getPrices() as $packagePrice) {
            /** @var PackagePrice $packagePrice */
            $totalPrice += $packagePrice->getPrice();
        }

        return $totalPrice;
    }

    public function getNote()
    {
        foreach ($this->packageDataXMLElement->PerDayRates->PerDayRate as $perDayRate) {
            if (isset($perDayRate->attributes()['promoName'])) {
                $promoName = (string)$perDayRate->attributes()['promoName'];
                $this->addPackageNote($promoName,
                    $this->translator->trans('package_info.expedia.promoName',
                        ['%dateString%' => (string)$perDayRate->attributes()['stayDate']]));
            }
        }

        foreach ($this->packageDataXMLElement->GuestCount->Child as $childNode) {
            /** @var \SimpleXMLElement $childNode */
            $this->addPackageNote($childNode->attributes()['age'], $this->translator->trans('package_info.expedia.child_age'));
        }

        return $this->note;
    }

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
    }

    public function getTourists()
    {
        return [$this->payer];
    }

    /**
     * @param $isSmoking
     * @return ExpediaPackageInfo
     */
    public function setIsSmoking($isSmoking)
    {
        $this->isSmoking = $isSmoking;

        return $this;
    }

    public function getIsSmoking()
    {
        return $this->isSmoking;
    }

    /**
     * @param $channelManagerId
     * @return ExpediaPackageInfo
     */
    public function setChannelManagerId($channelManagerId)
    {
        $this->channelManagerId = $channelManagerId;

        return $this;
    }

    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }
}