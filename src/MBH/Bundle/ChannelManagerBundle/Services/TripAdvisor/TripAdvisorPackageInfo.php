<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TripAdvisorPackageInfo extends AbstractPackageInfo
{
    private $isCorrupted;
    private $checkInDate;
    private $checkOutDate;
    private $bookingMainData;
    private $bookingSessionId;
    /** @var  Tourist $payer */
    private $payer;
    private $tariff;
    private $roomType;
    private $childAges;
    private $childrenCount;
    private $adultsCount;
    private $travellerData;

    private $isPricesInit = false;
    private $prices = [];

    public function setInitData(
        $checkInDate,
        $checkOutDate,
        $bookingMainData,
        $bookingSessionId,
        $payer,
        Tariff $tariff,
        RoomType $roomType,
        $childAges,
        $childrenCount,
        $adultsCount,
        $travellerData
    ) {
        $this->checkOutDate = $checkOutDate;
        $this->checkInDate = $checkInDate;
        $this->bookingMainData = $bookingMainData;
        $this->bookingSessionId = $bookingSessionId;
        $this->payer = $payer;
        $this->tariff = $tariff;
        $this->roomType = $roomType;
        $this->childAges = $childAges;
        $this->childrenCount = $childrenCount;
        $this->adultsCount = $adultsCount;
        $this->travellerData = $travellerData;

        return $this;
    }

    public function getBeginDate()
    {
        return Helper::getDateFromString($this->checkInDate, 'Y-m-d');
    }

    public function getEndDate()
    {
        return Helper::getDateFromString($this->checkOutDate, 'Y-m-d');
    }

    public function getRoomType(): RoomType
    {
        return $this->roomType;
    }

    public function getTariff()
    {
        return $this->tariff;
    }

    public function getAdultsCount()
    {
        return $this->adultsCount;
    }

    public function getChildrenCount()
    {
        return $this->childrenCount;
    }

    public function getPrices()
    {
        if (!$this->isPricesInit) {
            $estimatedAdultsChildrenCounts = $this->getRoomType()->getAdultsChildrenCombination($this->getAdultsCount(),
                $this->getChildrenCount());
            $childrenAdultsString = $estimatedAdultsChildrenCounts['adults'] . '_' . $estimatedAdultsChildrenCounts['children'];
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

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
    }

    public function getTourists()
    {
        $tourists = [];
        if (count($this->travellerData) > 0) {
            $firstName = trim($this->travellerData['traveler_first_name']);
            $lastName = trim($this->travellerData['traveler_last_name']);
            if ($this->payer->getFirstName() == $firstName && $this->payer->getLastName() == $lastName) {
                $tourist = $this->payer;
            } else {
                $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    $lastName,
                    $firstName
                );
            }

            $this->dm->persist($tourist);
            $this->dm->flush();

            $tourists[] = $tourist;
        }

        return $tourists;
    }

    public function getIsSmoking()
    {
        return false;
    }

    public function getChannelManagerId()
    {
        return $this->bookingSessionId;
    }

    public function getChildAges()
    {
        return $this->childAges;
    }
}