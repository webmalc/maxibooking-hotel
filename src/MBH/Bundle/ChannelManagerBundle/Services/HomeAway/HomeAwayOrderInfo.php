<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\CashBundle\Document\CashDocument;
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

    private $isCashDocumentsInit = false;
    private $cashDocuments;

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
        //TODO: Может не быть
        return trim((string)$this->bookingData->inquiryId);
    }

    public function getPrice()
    {
        $price = 0;
        foreach ($this->bookingData->orderItemList->orderItem as $orderItem) {
            /** @var \SimpleXMLElement $orderItem */
            $totalPriceInCurrency = trim((float)$orderItem->totalAmount);
            $currency = (string)$orderItem->totalAmount->attributes()['currency'];
            $price += $this->getPriceInLocaleCurrency($totalPriceInCurrency, $currency);
        }

        return $price;
    }
    
    public function getCashDocuments(Order $order) 
    {
        if (!$this->isCashDocumentsInit) {
            //TODO: Уточнить
            $payMethod = !is_null($this->getCreditCard()) ? 'electronic' : 'cash';

            foreach ($this->bookingData->orderItemList->orderItem as $orderItem) {
                /** @var \SimpleXMLElement $orderItem */
                $totalPriceInCurrency = trim((float)$orderItem->totalAmount);
                $operation = $totalPriceInCurrency > 0 ? 'in' : 'out';
                $currency = (string)$orderItem->totalAmount->attributes()['currency'];

                //Если не указан статус оплаты - значит статус "ACCEPTED"
                $isPaid = empty($orderItem->status) || trim((string)$orderItem->status) == 'ACCEPTED';
                $this->cashDocuments[] = (new CashDocument())
                    ->setMethod($payMethod)
                    ->setOperation($operation)
                    ->setOrder($order)
                    ->setTouristPayer($this->getPayer())
                    ->setTotal($this->getPriceInLocaleCurrency(abs($totalPriceInCurrency), $currency))
                    ->setIsPaid($isPaid);
            }
            $this->isCashDocumentsInit = true;
        }
        
        return $this->cashDocuments;
    }

    private function getPriceInLocaleCurrency($price, $currency)
    {
        $localeCurrency = $this->container->getParameter('locale.currency');
        return $currency != strtoupper($localeCurrency)
            //TODO: Сменить на конвертирование из локальной валюты
            ? $this->container->get('mbh.currency')->convertToRub($price, $currency)
            : $price;
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
                ->setPrice($this->getPrice())
                ->setTourists([$this->getPayer()])
        ];
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
        return 'homeaway';
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