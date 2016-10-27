<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfoService;

class OrderInfo extends AbstractOrderInfoService
{
    /** @var  ContainerInterface $container */
    private $container;
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var ChannelManagerConfigInterface $config */
    private $config;
    /** @var \SimpleXMLElement $orderDataXMLElement */
    private $orderDataXMLElement;
    private $roomTypes;
    private $tariffs;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    public function setInitData($orderInfoArray, $config, $tariffs, $roomTypes)
    {
        $this->config = $config;
        $this->orderDataXMLElement = $orderInfoArray;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;
        return $this;
    }

    public function getChannelManagerOrderId()
    {
        return $this->getCommonOrderData('id');
    }

    public function getOrderStatus()
    {
        return $this->getCommonOrderData('status');
    }

    public function getHotelId()
    {
        return $this->orderDataXMLElement->Hotel->attributes()['id'];
    }

    private function getCommonOrderData($param)
    {
        return (string)$this->orderDataXMLElement->attributes()[$param];
    }

    public function getPayer() : Tourist
    {
        /** @var \SimpleXMLElement $primaryGuestDataElement */
        $primaryGuestDataElement = $this->orderDataXMLElement->PrimaryGuest;

        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$primaryGuestDataElement->Name->attributes['surname'],
            (string)$primaryGuestDataElement->Name->attributes['givenName'],
            null,
            null,
            null,
            (string)$primaryGuestDataElement->Phone->attributes['number']
        );

        return $payer;
    }

    /**
     * @return PackageInfo
     */
    public function getPackagesData()
    {
        $packageData = $this->orderDataXMLElement->RoomStay;

        return $this->container->get('mbh.channelmanager.expedia_package_info')
            ->setInitData($packageData, $this->config, $this->tariffs, $this->roomTypes);
    }

    public function getCashDocuments()
    {
        //TODO: Реализовать
//        $fee = new CashDocument();
//        $fee->setIsConfirmed(false)
//            ->setIsPaid(true)
//            ->setMethod('electronic')
//            ->setOperation('fee')
//            ->setOrder($order)
//            ->setTouristPayer($orderInfo->getPayer())
//            ->setTotal($orderInfo->getOrderPrice());
//        if ($orderInfo->getPayType() == 2) {
//            $fee->setIsPaid(false);
//        }
    }

    public function getChannelManagerDisplayedName()
    {
        return $this->getCommonOrderData('source');
    }

    public function isOrderModified()
    {
        return $this->checkOrderStatusType('Modify');
    }

    public function isOrderCreated()
    {
        return $this->checkOrderStatusType('Book');
    }

    public function isOrderCancelled()
    {
        return $this->checkOrderStatusType('Cancel');
    }

    private function checkOrderStatusType($status)
    {
        return (string)$this->orderDataXMLElement->attributes()['type'] === $status ? true : false;
    }

    public function getServices()
    {
        foreach ($this->orderDataXMLElement->SpecialRequest as $serviceXMLElement) {
            //TODO: Реализовать
        }
    }

    public function getPrice()
    {
        return $this->getPackagesData()->getPrice();
    }

    public function getOriginalPrice()
    {
        return $this->getPackagesData()->getOriginalPrice();
    }
}