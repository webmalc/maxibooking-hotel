<?php

namespace MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels;

use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderInfo
{
    private $orderData;
    private $tariffs;
    private $roomTypes;
    private $dm;
    private $container;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    public function setInitData($bookingInfoArray, $config, $tariffs, $roomTypes)
    {
        $this->config = $config;
        $this->orderData = $bookingInfoArray;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;
        return $this;
    }

    public function getContactLastname()
    {
        return (string)$this->orderData['contact_last_name'];
    }

    public function getBookingId()
    {
        return $this->orderData['id'];
    }

    public function getUserComment()
    {
        return $this->orderData['description'];
    }

    public function getPayType()
    {
        return $this->orderData['pay_type'];
    }

    public function getChannelManagerId()
    {
        return (string)$this->orderData['id'];
    }

    public function getPayerName()
    {
        return $this->orderData['contact_name'];
    }

    public function getLastAction()
    {
        return (string)$this->orderData['last_action'];
    }

    public function getOrderState()
    {
        return (string)$this->orderData['state'];
    }

    public function getModifiedDate()
    {
        return $this->orderData['modified'];
    }

    public function getPayer()
    {
        $touristFirstNameParts = explode(' ', trim((string)$this->orderData['contact_first_name']));

        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$this->orderData['contact_last_name'],
            $touristFirstNameParts[0],
            isset($touristFirstNameParts[1]) ? $touristFirstNameParts[1] : null,
            null,
            isset($this->orderData['contact_email']) ? (string)$this->orderData['contact_email'] : null,
            isset($this->orderData['contact_phone']) ? (string)$this->orderData['contact_phone'] : null
        );

        return $payer;
    }

    public function getPackages()
    {
        $packages = [];

        //Разбиваем получаемый массив данных о размещениях по типам размещений
        $roomTypesData = [];
        foreach ($this->orderData['rooms'] as $currentDatePlacementData) {
            $roomTypesData[$currentDatePlacementData['placement_id']][] = $currentDatePlacementData;
        }

        //Разбиваем получаемый массив данных о гостях по типам размещений гостей
        $guests = [];
        foreach ($this->orderData['guests'] as $guest) {
            $guests[$guest['placement_id']][] = $guest;
        }

        foreach ($roomTypesData as $roomType) {
            //В одном заказе для одного типа размещения кол-во используемых комнат одинаково, поэтому берем первый элемент массива
            for ($i = 0; $i < (int)$roomType[0]['qty']; $i++) {
                $occupantsCount = $roomType[0]['occupants'];
                $placementId = $roomType[0]['placement_id'];
                $guestArrayOffset = $i * $occupantsCount;
                $currentPackageGuests = array_slice($guests[$placementId], $guestArrayOffset,  $occupantsCount);
                $packages[] = $this->container->get('mbh.channelmanager.hoh_package_info')
                    ->setInitData($roomType, $currentPackageGuests, $this->config, $this->tariffs, $this->roomTypes);
            }
        }

        return $packages;
    }

    public function getOrderPrice()
    {
        return (float)$this->orderData['sum'];
    }

}