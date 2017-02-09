<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

use MBH\Bundle\PackageBundle\Document\PackagePrice;

class ExpediaPackageInfo extends AbstractPackageInfo
{

    /** @var ChannelManagerConfigInterface $config */
    private $config;
    /**
     * Данные о брони, в виде объекта SimpleXMLElement
     * @var \SimpleXMLElement $orderDataXMLElement
     */
    private $packageDataXMLElement;
    private $roomTypes;
    private $tariffs;
    private $payer;
    private $isPricesInit = false;
    private $prices = [];
    private $isSmoking = false;
    private $channelManagerId;

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
                $price = (float)$perDayRate->attributes()['baseRate'];
                $this->prices[] = new PackagePrice($currentDate, $price, $this->getTariff());
            }
            $this->isPricesInit = true;
        }
        return $this->prices;
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
        $serviceTariffId = $this->getPackageCommonData('ratePlanID');
        if (isset($this->tariffs[$serviceTariffId])) {
            $tariff = $this->tariffs[$serviceTariffId]['doc'];
        } else {
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
                [
                    'hotel.id' => $this->config->getHotelId(),
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $this->addPackageNote($this->translator->trans('services.expedia.invalid_tariff_id'));
            $this->isCorrupted = true;

        }
        if (!isset($tariff)) {
            throw new \Exception($this->translator->trans('services.expedia.nor_one_tariff'));
        }

        return $tariff;
    }

    public function getBeginDate() : \DateTime
    {
        $prices = $this->getPrices();
        /** @var PackagePrice $firstPackagePrice */
        $firstPackagePrice = current($prices);

        return $firstPackagePrice->getDate();
    }

    public function getEndDate() : \DateTime
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