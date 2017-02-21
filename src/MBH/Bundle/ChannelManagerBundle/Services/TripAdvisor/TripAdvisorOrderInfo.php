<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;

class TripAdvisorOrderInfo extends AbstractOrderInfo
{
    private $checkInDate;
    private $checkOutDate;
    private $hotelId;
    private $customerData;
    private $roomsData;
    private $specialRequests;
    private $paymentData;
    private $finalPriceAtBooking;
    private $finalPriceAtCheckout;
    private $bookingMainData;
    private $bookingSession;

    public function setInitData(
        $checkInDate,
        $checkOutDate,
        $hotelId,
        $customerData,
        $roomsData,
        $specialRequests,
        $paymentData,
        $finalPriceAtBooking,
        $finalPriceAtCheckout,
        $bookingMainData,
        $bookingSession
    ) {
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->hotelId = $hotelId;
        $this->customerData = $customerData;
        $this->roomsData = $roomsData;
        $this->specialRequests = $specialRequests;
        $this->paymentData = $paymentData;
        $this->finalPriceAtBooking = $finalPriceAtBooking;
        $this->finalPriceAtCheckout = $finalPriceAtCheckout;
        $this->bookingMainData = $bookingMainData;
        $this->bookingSession = $bookingSession;

        return $this;
    }

    public function getPayer() : Tourist
    {
        $lastName = (string)$this->customerData['last_name'];
        $firstName = (string)$this->customerData['first_name'];
        $phoneNumber = (string)$this->customerData['phone_number'];
        $email = (string)$this->customerData['email'];
        $country = (string)$this->customerData['country'];

        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            $lastName,
            $firstName,
            null,
            null,
            $email,
            $phoneNumber,
            $country
        );

        return $payer;
    }

    public function getChannelManagerOrderId() : ?string
    {
        return (string)$this->bookingSession;
    }

    public function getPrice()
    {
        return (float)$this->finalPriceAtCheckout['amount'];
    }

    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        $cashDocument = new CashDocument();
        $cashDocuments[] = $cashDocument->setIsConfirmed(false)
            ->setIsPaid(true)
            ->setMethod('electronic')
            ->setOperation('in')
            ->setOrder($order)
            ->setTouristPayer($this->getPayer())
            ->setTotal($this->getPrice());

        return $cashDocuments;
    }

    public function getSource() : ?PackageSource
    {
        return $this->dm->getRepository('MBHPackageBundle:PackageSource')
            ->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        $packagesData = [];
        foreach ($this->roomsData as $roomData) {
            $packagesData[] = $this->container->get('mbh.channel_manager.trip_advisor_package_info')
                ->setInitData($roomData, $this->checkInDate, $this->checkOutDate, $this->bookingMainData,
                    $this->bookingSession);
        }

        return $packagesData;
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
        $card = new CreditCard();

        $card->setNumber($this->paymentData['card_number'])
            ->setDate($this->paymentData['expiration_month'] . '/' . $this->paymentData['expiration_year'])
            ->setCvc($this->paymentData['cvv'])
            ->setCardholder($this->paymentData['cardholder_name'])
            ->setType($this->paymentData['card_type']);

        return $card;
    }

    public function getChannelManagerName() : string
    {
        return 'tripadvisor';
    }

    public function isOrderModified() : bool
    {
        return false;
    }

    public function isOrderCreated() : bool
    {
        return true;
    }

    public function isOrderCancelled() : bool
    {
        return false;
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsNew(?Order $order) : bool
    {
        return true;
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsModified(?Order $order) : bool
    {
        return false;
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandleAsCancelled(?Order $order) : bool
    {
        return false;
    }

    public function getNote() : string
    {
        return $this->specialRequests ? $this->specialRequests : '';
    }
}