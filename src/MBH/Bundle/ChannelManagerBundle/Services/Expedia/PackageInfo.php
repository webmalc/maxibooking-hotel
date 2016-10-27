<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

class PackageInfo extends AbstractPackageInfo
{
    /** @var  ContainerInterface $container */
    private $container;
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var ChannelManagerConfigInterface $config */
    private $config;
    /**
     * Данные о брони, в виде SimpleXML-элемента
     * @var \SimpleXMLElement $orderDataXMLElement
     */
    private $packageDataXMLElement;
    private $roomTypes;
    private $tariffs;
    private $errorMessage = '';
    private $isCorrupted = false;
    private $isPricesInit = false;
    private $prices = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    public function setInitData($packageData, $config, $tariffs, $roomTypes)
    {
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
            foreach ($this->packageDataXMLElement->PerDayRates as $perDayRate) {
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
            $this->errorMessage .= $this->container->get('translator')
                ->trans('services.expedia.invalid_room_type_id');
            $this->isCorrupted = true;
        }
        if (!$roomType) {
            throw new \Exception($this->container->get('translator')
                ->trans('services.expedia.nor_one_room_type'));
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
            $this->errorMessage .= $this->container->get('translator')->trans('services.expedia.invalid_tariff_id');
            $this->isCorrupted = true;

        }
        if (!isset($tariff)) {
            throw new \Exception($this->container->get('translator')->trans('services.expedia.nor_one_tariff'));
        }
        return $tariff;
    }

    public function getBeginDate()
    {
        /** @var PackagePrice $firstPackagePrice */
        $firstPackagePrice = current($this->getPrices());
        return $firstPackagePrice->getDate();
    }

    public function getEndDate()
    {
        /** @var PackagePrice $lastPackagePrice */
        $lastPackagePrice = end($this->getPrices());
        return date_add($lastPackagePrice->getDate(), new \DateInterval('P1D'));
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
        // TODO: Implement getNote() method.
    }

    public function getIsCorrupted()
    {
        // TODO: Implement getIsCorrupted() method.
    }

    public function getTourists()
    {
        // TODO: Implement getTourists() method.
    }

}