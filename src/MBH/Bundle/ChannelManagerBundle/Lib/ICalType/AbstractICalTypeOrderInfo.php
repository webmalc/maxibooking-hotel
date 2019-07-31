<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Document\AbstractICalTypeChannelManagerRoom;

abstract class AbstractICalTypeOrderInfo extends AbstractOrderInfo
{
    /** @var array */
    protected $orderData;

    /** @var AbstractICalTypeChannelManagerRoom */
    protected $room;

    /** @var Tariff */
    protected $tariff;

    /** @var array */
    protected $packagesData;

    /** @var bool */
    protected $isPackagesDataInit = false;

    abstract protected function getPackageInfoService(): AbstractICalTypePackageInfo;
    abstract public function getDepartureDate(): ?string;

    /**
     * @param array $orderData
     * @param AbstractICalTypeChannelManagerRoom $room
     * @param Tariff $tariff
     * @return self
     */
    public function setInitData(array $orderData, AbstractICalTypeChannelManagerRoom $room, Tariff $tariff): self
    {
        $this->orderData = $orderData;
        $this->room = $room;
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

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->getPackagesData()[0]->getPrice();
    }

    protected function setPackagesData(): void
    {
        $this->packagesData = [
            $this->getPackageInfoService()
                ->setInitData($this->orderData, $this->room, $this->tariff)
        ];
        $this->isPackagesDataInit = true;
    }

    /**
     * @param Order $order
     * @return array|CashDocument[]
     * @throws \Exception
     */
    public function getCashDocuments(Order $order): array
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

    /**
     * @return PackageSource|null
     */
    public function getSource(): ?PackageSource
    {
        return $this->dm
            ->getRepository(PackageSource::class)
            ->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData(): array
    {
        if (!$this->isPackagesDataInit) {
            $this->packagesData = [
                $this->getPackageInfoService()
                    ->setInitData($this->orderData, $this->room, $this->tariff)
            ];
            $this->isPackagesDataInit = true;
        }

        return $this->packagesData;
    }

    /**
     * @return PackageService[]
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    public function getCreditCard(): ?CreditCard
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isOrderModified(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isOrderCreated(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
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
}
