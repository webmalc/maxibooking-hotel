<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

class ExpediaNotificationPackageInfo extends AbstractPackageInfo
{
    const ADULTS_AGE_QUALIFYING_CODE = 10;
    const CHILDREN_AGE_QUALIFYING_CODE = 8;

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
    private $prices = [];
    private $isPricesInit = false;

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

    /**
     * @return bool|\DateTime
     */
    public function getBeginDate()
    {
        return \DateTime::createFromFormat('Y-m-d', (string)$this->packageDataXMLElement->TimeSpan->attributes()['Start']);
    }

    /**
     * @return bool|\DateTime
     */
    public function getEndDate()
    {
        return \DateTime::createFromFormat('Y-m-d', (string)$this->packageDataXMLElement->TimeSpan->attributes()['End']);
    }

    public function getRoomType(): RoomType
    {
        if (!$this->isRoomTypeInit) {
            $roomTypeId = (string)$this->packageDataXMLElement->RoomTypes->RoomType->attributes()['RoomTypeCode'];
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
            $serviceTariffId = (string)$firstRatePlansElement->attributes()['RatePlanCode'];
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

    /**
     * @return int
     */
    public function getAdultsCount()
    {
        return $this->getNumberOfGuestsByCode(self::ADULTS_AGE_QUALIFYING_CODE);
    }

    /**
     * @return int
     */
    public function getChildrenCount()
    {
        return $this->getNumberOfGuestsByCode(self::CHILDREN_AGE_QUALIFYING_CODE);
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        if (!$this->isPricesInit) {
            foreach ($this->packageDataXMLElement->RoomRates->RoomRate as $roomRateNode) {
                /** @var \SimpleXMLElement $rateNode */
                $rateNode = $roomRateNode->Rates->Rate;
                $currentDate = \DateTime::createFromFormat('Y-m-d', $rateNode->attributes()['EffectiveDate']);
                $price = (float)$rateNode->Base->attributes()['AmountBeforeTax'];
                if ((string)$rateNode->AdditionalGuestAmounts !== '') {
                    foreach ($rateNode->AdditionalGuestAmounts->AdditionalGuestAmount as $additionalGuestAmountNode) {
                        $price += $additionalGuestAmountNode->Amount->attributes()['AmountBeforeTax'];
                    }
                }

                $this->prices[] = new PackagePrice($currentDate, $price, $this->getTariff());
            }

            $this->isPricesInit = true;
        }

        return $this->prices;
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
        return $this->note;
    }

    /**
     * @return bool
     */
    public function getIsCorrupted()
    {
        return $this->isCorrupted;
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

    private function getNumberOfGuestsByCode($qualifyingCode)
    {
        $numberOfAdults = 0;
        /** @var \SimpleXMLElement $guestCountsNode */
        foreach ($this->packageDataXMLElement->GuestCounts->GuestCount as $guestCountsNode) {
            if ($guestCountsNode->attributes()['AgeQualifyingCode'] == $qualifyingCode) {
                $numberOfAdults += (int)$guestCountsNode->attributes()['Count'];
            }
        }

        return $numberOfAdults;
    }
}