<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;


use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;

interface ICalTypeOrderInfoInterface
{
    public function setInitData(array $orderData, AbstractICalTypeChannelManagerRoom $airbnbRoom, Tariff $tariff): self;
    public function getPayer(): Tourist;
    public function getChannelManagerOrderId(): string;
    public function getPrice();
    public function getCashDocuments(Order $order);
    public function getSource(): ?PackageSource;
    public function getPackagesData();
    public function getServices();
    public function getCreditCard();
    public function getChannelManagerName(): string;
    public function isOrderModified(): bool;
    public function isOrderCreated(): bool;
    public function isOrderCancelled(): bool;
    public function isHandledAsNew(?Order $order): bool;
    public function isHandledAsModified(?Order $order): bool;
    public function isHandledAsCancelled(?Order $order): bool;
    public function getNote(): string;
    public function getOrderData(): array;
}
