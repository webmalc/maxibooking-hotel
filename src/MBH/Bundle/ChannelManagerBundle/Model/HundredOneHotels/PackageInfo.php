<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

class PackageInfo
{
    private $roomTypeInfo;
    private $guests;
    private $packageCommonData;
    private $tariffs;
    private $roomTypes;
    /** @var DocumentManager  */
    private $dm;
    /** @var ContainerInterface  */
    private $container;
    /** @var ChannelManagerConfigInterface $config */
    private $config;
    private $errorMessage = '';
    private $isCorrupted = false;
    private $isTariffInit;
    private $tariff;
    private $isRoomTypeInit;
    private $roomType;
    private $isTotalPriceInit;
    private $totalPrice = 0;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    public function setInitData($roomTypeInfo, $guests, $config, $tariffs, $roomTypes)
    {
        $this->roomTypeInfo = $roomTypeInfo;
        $this->guests = $guests;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;
        $this->config = $config;
        //Данные о размещении одинаковы для всех элементов массива, представляющего данные о размещениях по дням
        $this->packageCommonData = $roomTypeInfo[0];
        return $this;
    }

    public function getRoomType()
    {
        if (!$this->isRoomTypeInit) {
            $roomTypeId = (string)$this->packageCommonData['room_id'];
            if (isset($this->roomTypes[$roomTypeId])) {
                $this->roomType = $this->roomTypes[$roomTypeId]['doc'];
            } else {
                $this->roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                    [
                        'hotel.id' => $this->config->getHotelId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $this->errorMessage .= $this->container->get('translator')
                    ->trans('services.hundredOneHotels.invalid_room_type_id', ['%id%' => $roomTypeId]);
                $this->isCorrupted = true;
            }
            if (!$this->roomType) {
                throw new \Exception($this->container->get('translator')
                    ->trans('services.hundredOneHotels.nor_one_room_type'));
            }
            $this->isRoomTypeInit = true;
        }
        return $this->roomType;
    }

    public function getTariff()
    {
        if (!$this->isTariffInit) {
            $placementId = $this->packageCommonData['placement_id'];
            if (isset($this->tariffs[$placementId])) {
                $this->tariff = $this->tariffs[$placementId]['doc'];
            } else {
                $this->tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
                    [
                        'hotel.id' => $this->config->getHotelId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $this->errorMessage .= $this->container->get('translator')
                    ->trans('services.hundredOneHotels.invalid_tariff_id', ['%id%' => $placementId]);
                $this->isCorrupted = true;

            }
            if (!isset($this->tariff)) {
                throw new \Exception($this->container->get('translator')
                    ->trans('services.hundredOneHotels.nor_one_tariff'));
            }
            $this->isTariffInit = true;
        }
        return $this->tariff;
    }

    public function getPackagePrices()
    {
        $packagePrices = [];
        foreach ($this->roomTypeInfo as $currentDatePlacementData) {
            $currentDate = \DateTime::createFromFormat('Y-m-d', $currentDatePlacementData['day']);
            $price = (int)$currentDatePlacementData['price'];
            $packagePrices[] = new PackagePrice($currentDate, $price, $this->getTariff());
        }
        return $packagePrices;
    }

    public function getTotalPrice()
    {
        if (!$this->isTotalPriceInit) {
            $packagePrices = $this->getPackagePrices();
            foreach ($packagePrices as $packagePrice) {
                /** PackagePrice $packagePrice */
                $this->totalPrice += $packagePrice->getPrice();
            }
            $this->isTotalPriceInit = true;
        }
        return $this->totalPrice;
    }

    public function getBeginDate()
    {
        return $this->getDateFromString($this->packageCommonData['day']);
    }

    public function getEndDate()
    {
        $endDate = $this->getDateFromString(end($this->roomTypeInfo)['day']);
        date_add($endDate, new \DateInterval('P1D'));
        return $endDate;
    }

    private function getDateFromString($dateString)
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString . ' 00:00:00');
        return $date;
    }

    public function getTourists()
    {
        $tourists = [];
        /** @var TouristRepository $touristRepository */
        $touristRepository = $this->dm->getRepository('MBHPackageBundle:Tourist');
        foreach ($this->guests as $guestData) {
            $touristNameData = explode(' ', $guestData['name']);
            $tourists[] = $touristRepository->fetchOrCreate(
                $touristNameData[0],
                isset($touristNameData[1]) ? $touristNameData[1] : null,
                isset($touristNameData[2]) ? $touristNameData[2] : null
            );
        }
        return $tourists;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getOccupantsCount()
    {
        return (int)$this->packageCommonData['occupants'];
    }

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
    }
}