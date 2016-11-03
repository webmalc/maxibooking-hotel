<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerException;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\CashBundle\Document\CashDocument;

class ExpediaOrderInfo extends AbstractOrderInfo
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
    private $isPackagesDataInit = false;
    private $packagesData = [];
    private $orderNote = '';
    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->translator = $container->get('translator');
    }

    public function setInitData($orderInfoElement, $config, $tariffs, $roomTypes)
    {
        $this->config = $config;
        $this->orderDataXMLElement = $orderInfoElement;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;
        return $this;
    }

    public function getChannelManagerOrderId()
    {
        return (int)$this->getCommonOrderData('id');
    }

    public function getHotelId()
    {
        return (int)$this->getMandatoryDataByXPath($this->orderDataXMLElement, '/Booking/Hotel/@id',
            $this->translator->trans('order_info.expedia.required_hotel_id'));
    }

    private function getCommonOrderData($param)
    {
        return $this->getMandatoryDataByXPath($this->orderDataXMLElement, "/Booking/@$param",
            $this->translator->trans('order_info.expedia.required_order_data', ['%dataAttribute%' => $param]));
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


    public function getPackagesData() {
        if (!$this->isPackagesDataInit) {

            $packageDataElement = $this->orderDataXMLElement->RoomStay;

            //Создаем и добавляем объект, хранящий данные о брони. Добавляется один, так как в принимаемых из expedia заказах содержится только 1 бронь
            $this->packagesData[] = $this->container->get('mbh.channelmanager.expedia_package_info')
                ->setInitData($packageDataElement, $this->config, $this->tariffs, $this->roomTypes, $this->getPayer())
                ->setIsSmoking($this->getIsSmokingValue());

            $this->isPackagesDataInit = true;
        }
        return $this->packagesData;
    }

    private function getIsSmokingValue()
    {
        $isSmokingElement = $this->orderDataXMLElement->xpath('/SpecialRequest[starts-with(@code, "2")]');

        $isSmoking = false;
        if ($isSmokingElement) {
            $isSmokingString = (string)$isSmokingElement;
            //2.2 Smoking
            if ($isSmokingString === '2.2') {
                $isSmoking = true;
            }
        }

        return $isSmoking;
    }

    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        /** @var \SimpleXMLElement $cardElement */
        //Если указаны данные платежной карты, то создаю кассовые документы для платежа и комисии
        $cardElement = $this->orderDataXMLElement->RoomStay->PaymentCard;
        if ($cardElement !== null) {

            $cashDocument = new CashDocument();
            $cashDocuments[] = $cashDocument->setIsConfirmed(false)
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal($this->getPrice());
            $this->orderNote .= $this->translator->trans('order_info.expedia.need_сonfirm_cash_payment_document') . "\n";

            $amountOfTaxes = $this->orderDataXMLElement->xpath('/RoomStay/Total/@amountOfTaxes');
            if ($amountOfTaxes !== null) {
                $cashDocument = new CashDocument();
                $cashDocuments[] = $cashDocument->setIsConfirmed(false)
                    ->setIsPaid(true)
                    ->setMethod('electronic')
                    ->setOperation('fee')
                    ->setOrder($order)
                    ->setTouristPayer($this->getPayer())
                    ->setTotal($amountOfTaxes);
                $this->orderNote .= $this->translator->trans('order_info.expedia.need_сonfirm_cash_tax_document') . "\n";
            }
        }

        return $cashDocuments;
    }

    public function getChannelManagerDisplayedName()
    {
        return (string)$this->getCommonOrderData('source');
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

    /**
     * Может быть 'Book', 'Modify', 'Cancel'
     * @return string
     */
    public function getOrderStatusType()
    {
        return (string)$this->getCommonOrderData('type');
    }

    private function checkOrderStatusType($status)
    {
        return $this->getOrderStatusType() === $status;
    }

    public function getPrice()
    {
        return current($this->getPackagesData())->getPrice();
    }

    public function getOriginalPrice()
    {
        return current($this->getPackagesData())->getOriginalPrice();
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsNew(Order $order)
    {
        return $this->checkOrderStatusType('Book') && !$order;
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsModified(Order $order)
    {
        return $this->isOrderModified() && $order;
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandleAsCancelled(Order $order)
    {
        return $this->isOrderCancelled() && $order;
    }

    /**
     * Возвращает время изменения заказа.
     * @return \DateTime|null
     */
    public function getModifiedDate()
    {
        return null;
    }

    /**
     * Возвращает данные о кредитной карте, если указаны.
     * @return CreditCard|null
     */
    public function getCreditCard()
    {
        /** @var \SimpleXMLElement $cardElement */
        $cardElement = $this->orderDataXMLElement->RoomStay->PaymentCard;
        $card = null;

        if ($cardElement !== null) {
            $cardElementAttributes = $cardElement->attributes();
            $card = new CreditCard();

            $card->setNumber($cardElementAttributes['cardNumber'])
                ->setDate($cardElementAttributes['expireDate'])
                ->setCvc($cardElementAttributes['seriesCode'])
                ->setCardholder($cardElement->CardHolder->attributes()['name']);
        }

        return $card;
    }

    private function getMandatoryDataByXPath(\SimpleXMLElement $element, $xpath, $exceptionMessage)
    {
        $mandatoryData = $element->xpath($xpath);
        if ($mandatoryData) {
            return $mandatoryData[0];
        }

        throw new ChannelManagerException($this->translator->trans('order_info.expedia.required_data_missing',
            ['%elementDescription%' => $exceptionMessage]));
    }

    /**
     * Возвращает значение, необходимое для подтверждения получения брони с сервера
     * @return null|string
     */
    public function getConfirmNumber()
    {
        $confirmNumberElement = $this->orderDataXMLElement->xpath("/Booking/@confirmNumber");
        if ($confirmNumberElement) {

            return (string)$confirmNumberElement;
        }

        return null;
    }

    public function getNote()
    {
        foreach ($this->orderDataXMLElement->SpecialRequest as $specialRequest) {
            $codeString = (string)$specialRequest->attributes()['code'];
            $code = (int)$codeString[0];
            /**
             * 1.xx : bedding preferences, different codes for beddings
             * 4 : Free text
             */
            //TODO: Акции как обрабатывать?
            if ($code == 1 || $code == 4 || $code == 6) {
                $this->orderNote .= (string)$specialRequest . "\n";
            }
        }

        return $this->orderNote;
    }

    /**
     * @return PackageService[]
     */
    public function getServices()
    {
        return [];
    }
}