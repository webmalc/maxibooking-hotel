<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;

class AirbnbOrderInfo extends AbstractOrderInfo
{
    private $orderData;
    private $airbnbRoom;
    private $tariff;

    private $packagesData;
    private $isPackagesDataInit = false;

    /**
     * @param array $orderData
     * @param AirbnbRoom $airbnbRoom
     * @param Tariff $tariff
     * @return AirbnbOrderInfo
     */
    public function setInitData(array $orderData, AirbnbRoom $airbnbRoom, Tariff $tariff)
    {
        $this->orderData = $orderData;
        $this->airbnbRoom = $airbnbRoom;
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * @return Tourist
     * @throws \Exception
     */
    public function getPayer(): Tourist
    {
        return $this->getPackagesData()[0]->getTourists()[0];
    }

    public function getChannelManagerOrderId(): string
    {
        return $this->orderData['UID'];
    }

    public function getPrice()
    {
        return $this->getPackagesData()[0]->getPrice();
    }

    /**
     * @param Order $order
     * @return array|CashDocument[]
     * @throws \Exception
     */
    public function getCashDocuments(Order $order)
    {
        return [(new CashDocument())
            ->setIsConfirmed(false)
            ->setIsPaid(true)
            ->setMethod(CashDocument::METHOD_ELECTRONIC)
            ->setOperation(CashDocument::OPERATION_IN)
            ->setOrder($order)
            ->setTouristPayer($this->getPayer())
            ->setTotal($this->getPrice())];
    }

    public function getSource(): ?PackageSource
    {
        return $this->dm
            ->getRepository('MBHPackageBundle:PackageSource')
            ->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        if (!$this->isPackagesDataInit) {
            $this->packagesData = [$this->container
                ->get('mbh.airbnb_package_info')
                ->setInitData($this->orderData, $this->airbnbRoom, $this->tariff)];
            $this->isPackagesDataInit = true;
        }

        return $this->packagesData;
    }

    /**
     * @return PackageService[]
     */
    public function getServices()
    {
        return [];
    }

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    public function getCreditCard()
    {
        return null;
    }

    public function getChannelManagerName(): string
    {
        return Airbnb::NAME;
    }

    public function isOrderModified(): bool
    {
        return false;
    }

    public function isOrderCreated(): bool
    {
        return true;
    }

    public function isOrderCancelled(): bool
    {
        return false;
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandledAsNew(?Order $order): bool
    {
        return true;
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandledAsModified(?Order $order): bool
    {
        return false;
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandledAsCancelled(?Order $order): bool
    {
        return false;
    }

    public function getNote(): string
    {
        return $this->orderData['DESCRIPTION'] ?? '';
    }
}