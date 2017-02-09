<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;

class HomeAwayOrderInfo extends AbstractOrderInfo
{
    /** @var  \SimpleXMLElement $bookingData */
    private $bookingData;
    /** @var  HomeAwayConfig $config */
    private $config;

    /**
     * @param \SimpleXMLElement $bookingData
     * @param HomeAwayConfig $config
     * @return HomeAwayOrderInfo
     */
    public function setInitData(\SimpleXMLElement $bookingData, HomeAwayConfig $config) : HomeAwayOrderInfo
    {
        $this->bookingData = $bookingData;
        $this->config = $config;

        return $this;
    }

    public function getPayer() : Tourist
    {
        /** @var \SimpleXMLElement $inquirerElement */
        $inquirerElement = $this->bookingData->inquirer;
        $lastNameString = trim((string)$inquirerElement->lastName);
        $firstNameString = trim((string)$inquirerElement->firstName);
        $phoneNumberString = trim((string)$inquirerElement->phoneNumber);
        $emailString = trim((string)$inquirerElement->emailAddress);

        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            empty($lastNameString) ? $this->getChannelManagerOrderId() : $lastNameString,
            empty($firstNameString) ? null : $firstNameString,
            null,
            null,
            empty($emailString),
            empty($phoneNumberString) ? null : $phoneNumberString
        );

        return $payer;
    }

    public function getChannelManagerOrderId() : string
    {
        return trim((string)$this->bookingData->inquiryId);
    }

    public function getPrice()
    {
        // TODO: Implement getPrice() method.
    }

    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        $paymentData = $this->bookingData->paymentForm[0];
        $paymentDataNode = null;
        if (!empty($paymentData->paymentCard)) {
            $paymentDataNode = $paymentData->paymentCard;
        } elseif (!empty($paymentData->paymentInvoice)) {
            $paymentDataNode = $paymentData->paymentInvoice;
        }

        if (!is_null($paymentDataNode)) {

        }
    }

    public function getSource() : ?PackageSource
    {
        return $this->dm->getRepository('MBHPackageBundle:PackageSource')->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        return [
            $this->container->get('mbh.channelmanager.homeaway_package_info')
                ->setInitData($this->bookingData, $this->config)
        ];
    }

    /**
     * @return PackageService[]
     */
    public function getServices()
    {
        // TODO: Implement getServices() method.
    }

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    public function getCreditCard()
    {
        $card = null;
        $paymentCardData = $this->bookingData->paymentForm->paymentCard;
        $isCardSpecified = isset($paymentData);
        if ($isCardSpecified) {
            $card = new CreditCard();
            $cardTypeDescription = $paymentCardData->paymentCardDescriptor[0];
            $cardType = trim((string)$cardTypeDescription->cardCode) . ' ' 
                . trim((string)$cardTypeDescription->codeType) . ' '
                . trim((string)$cardTypeDescription->paymentFormType);

            $card->setNumber(trim((string)$paymentCardData->number))
                ->setDate(trim((string)$paymentCardData->expiration))
                ->setCvc(trim((string)$paymentCardData->cvv))
                ->setCardholder(trim((string)$paymentCardData->nameOnCard))
                ->setType($cardType);
        }

        return $card;
    }

    public function getChannelManagerName() : string
    {
        $channelName = $this->getChannelManagerDisplayedName();
        $underscoreLinePosition = strpos($channelName, '_');
        if ($underscoreLinePosition === false) {
            return $channelName;
        }

        return substr($channelName, 0, $underscoreLinePosition);
    }

    public function getChannelManagerDisplayedName() : string
    {
        return (string)$this->bookingData->listingChannel;
    }

    public function isOrderModified() : bool
    {
        // TODO: Implement isOrderModified() method.
    }

    public function isOrderCreated() : bool
    {
        // TODO: Implement isOrderCreated() method.
    }

    public function isOrderCancelled() : bool
    {
        // TODO: Implement isOrderCancelled() method.
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsNew(?Order $order) : bool
    {
        // TODO: Implement isHandleAsNew() method.
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsModified(?Order $order) : bool
    {
        // TODO: Implement isHandleAsModified() method.
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandleAsCancelled(?Order $order) : bool
    {
        // TODO: Implement isHandleAsCancelled() method.
    }

    public function getNote() : string
    {
        return trim((string)$this->bookingData->message);
    }
}