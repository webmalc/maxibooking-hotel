<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerException;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\CashBundle\Document\CashDocument;

class ExpediaOrderInfo extends AbstractOrderInfo
{
    //TODO: Генерировать, наверное нужно
    const DEFAULT_CONFIRM_NUMBER = '3202199119TZ';
    /** @var ChannelManagerConfigInterface $config */
    private $config;
    /** @var \SimpleXMLElement $orderDataXMLElement */
    private $orderDataXMLElement;
    private $roomTypes;
    private $tariffs;

    private $isPackagesDataInit = false;
    private $packagesData = [];
    private $payer;
    private $isPayerInit = false;
    private $source;
    private $isSourceInit = false;

    public function setInitData(\SimpleXMLElement $orderInfoElement, ExpediaConfig $config, $tariffs, $roomTypes)
    {
        $this->config = $config;
        $this->orderDataXMLElement = $orderInfoElement;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;

        return $this;
    }

    /**
     * @return string
     */
    public function getChannelManagerOrderId(): string
    {
        return (string)$this->getCommonOrderData('id');
    }

    /**
     * @return int
     */
    public function getHotelId()
    {
        return (int)$this->getMandatoryDataByXPath($this->orderDataXMLElement->Hotel[0], '@id',
            $this->translator->trans('order_info.expedia.required_hotel_id'));
    }

    /**
     * @param $param
     * @return \SimpleXMLElement
     */
    private function getCommonOrderData($param)
    {
        return $this->getMandatoryDataByXPath($this->orderDataXMLElement, "@$param",
            $this->translator->trans('order_info.expedia.required_order_data', ['%dataAttribute%' => $param]));
    }

    public function getPayer(): Tourist
    {
        if (!$this->isPayerInit) {
            /** @var \SimpleXMLElement $primaryGuestDataElement */
            $primaryGuestDataElement = $this->orderDataXMLElement->PrimaryGuest;
            $lastNameString = trim((string)$primaryGuestDataElement->Name->attributes()['surname']);
            $firstNameString = trim((string)$primaryGuestDataElement->Name->attributes()['givenName']);
            $lastName = empty($lastNameString) ? $this->getChannelManagerOrderId() : $lastNameString;
            $phoneNumber = null;
            if (isset($primaryGuestDataElement->Phone)) {
                $phoneAttributes = $primaryGuestDataElement->Phone->attributes();
                $phoneNumber = ((string)$phoneAttributes['countryCode'] !== '0' ? $phoneAttributes['countryCode'] : '')
                    . ((string)$phoneAttributes['cityAreaCode'] !== '0' ? $phoneAttributes['cityAreaCode'] : '')
                    . $phoneAttributes['number'];
            }
            $email = (string)$primaryGuestDataElement->Email ? (string)$primaryGuestDataElement->Email : null;

            $this->payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $lastName,
                empty($firstNameString) ? null : $firstNameString,
                null,
                null,
                $email,
                $phoneNumber
            );
            $this->isPayerInit = true;
        }
        return $this->payer;
    }

    /**
     * @return array
     */
    public function getPackagesData(): array
    {
        if (!$this->isPackagesDataInit) {
            $packageDataElement = $this->orderDataXMLElement->RoomStay[0];
            //Создаем и добавляем объект, хранящий данные о брони.
            // Добавляется один, так как в принимаемых из expedia заказах содержится только 1 бронь
            $this->packagesData[] = $this->container->get('mbh.channelmanager.expedia_package_info')
                ->setInitData($packageDataElement, $this->config, $this->tariffs, $this->roomTypes, $this->getPayer())
                ->setIsSmoking($this->getIsSmoking())
                ->setChannelManagerId($this->getChannelManagerOrderId());
            $this->isPackagesDataInit = true;
        }

        return $this->packagesData;
    }

    /**
     * @return bool
     */
    public function getIsSmoking(): bool
    {
        $isSmokingElement = $this->orderDataXMLElement->xpath('SpecialRequest[starts-with(@code, "2")]');
        $isSmoking = false;
        if ($isSmokingElement) {
            $isSmokingString = (string)$isSmokingElement[0];
            //2.2 Smoking
            if ($isSmokingString === 'Smoking') {
                $isSmoking = true;
            }
        }

        return $isSmoking;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        /** @var \SimpleXMLElement $cardElement */
        $sourceAttributeData = (string)$this->orderDataXMLElement->attributes()['source'];
        if (!($sourceAttributeData[0] == 'A')) {
            $cashDocument = new CashDocument();
            $cashDocuments[] = $cashDocument->setIsConfirmed(false)
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal($this->getPrice());
            $this->orderNote .= $this->translator->trans('order_info.expedia.need_сonfirm_cash_payment_document') . "\n";

            $amountOfTaxes = $this->orderDataXMLElement->xpath('RoomStay/Total/@amountOfTaxes');
            if ($amountOfTaxes) {
                $feeCashDocument = new CashDocument();
                $cashDocuments[] = $feeCashDocument->setIsConfirmed(false)
                    ->setIsPaid(true)
                    ->setMethod('electronic')
                    ->setOperation('fee')
                    ->setOrder($order)
                    ->setTouristPayer($this->getPayer())
                    ->setTotal((float)$amountOfTaxes[0]);

                //Входящий платеж всегда приходит с учетом коммисии(цена номера минус комиссия expedia), соответственно,
                //...при добавлении кассового документа комиссии, получается что она указывается дважды
                $inCashDocument = clone $feeCashDocument;
                $inCashDocument->setOperation('in');
                $cashDocuments[] = $inCashDocument;
                $this->orderNote .= $this->translator->trans('order_info.expedia.need_сonfirm_cash_tax_document') . "\n";
            }
        }

        return $cashDocuments;
    }

    /**
     * @return bool
     */
    public function isOrderModified(): bool
    {
        return $this->checkOrderStatusType('Modify');
    }

    /**
     * @return bool
     */
    public function isOrderCreated(): bool
    {
        return $this->checkOrderStatusType('Book');
    }

    /**
     * @return bool
     */
    public function isOrderCancelled(): bool
    {
        return $this->checkOrderStatusType('Cancel');
    }

    /**
     * Может быть 'Book', 'Modify', 'Cancel'
     * @return string
     */
    public function getOrderStatusType(): string
    {
        return (string)$this->getCommonOrderData('type');
    }

    /**
     * @param $status
     * @return bool
     */
    private function checkOrderStatusType($status): bool
    {
        return $this->getOrderStatusType() === $status;
    }

    /**
     * @return string
     */
    private function getOrderStatus()
    {
        return (string)$this->getCommonOrderData('status');
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return current($this->getPackagesData())->getPrice();
    }

    /**
     * @return mixed
     */
    public function getOriginalPrice()
    {
        return current($this->getPackagesData())->getOriginalPrice();
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandledAsNew(?Order $order): bool
    {
        return $this->checkOrderStatusType('Book') && !$order;
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandledAsModified(?Order $order): bool
    {
        return $this->isOrderModified() && $order;
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandledAsCancelled(?Order $order): bool
    {
        return $this->isOrderCancelled() && $order;
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

        if (!empty((string)$cardElement)) {
            $cardElementAttributes = $cardElement->attributes();
            $card = new CreditCard();

            $card->setNumber($cardElementAttributes['cardNumber'])
                ->setDate($cardElementAttributes['expireDate'])
                ->setCardholder($cardElement->CardHolder->attributes()['name']);

            if (!empty((string)$cardElementAttributes['seriesCode'])) {
                $card->setCvc($cardElementAttributes['seriesCode']);
            }
        }

        return $card;
    }

    /**
     * @param \SimpleXMLElement $element
     * @param $xpath
     * @param $exceptionMessage
     * @return \SimpleXMLElement
     * @throws ChannelManagerException
     */
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
     * @return string
     */
    public function getNote(): string
    {
        if (!empty((string)$this->orderDataXMLElement->SpecialRequest)) {
            foreach ($this->orderDataXMLElement->SpecialRequest as $specialRequest) {
                $codeString = (string)$specialRequest->attributes()['code'][0];
                $specialRequestString = (string)$specialRequest;
                /**
                 * 1.xx : bedding preferences, different codes for beddings
                 * 2.1 Non-smoking
                 * 2.2 Smoking
                 * 3 Multi room booking and Mixed Rate Bookings
                 * 4 Free text
                 * 5 payment instruction
                 * 6 Value Add Promotions
                 */
                switch (substr($codeString, 0, 1)) {
                    case "1":
                        $this->addOrderNote($specialRequestString, 'order_info.expedia.bedding_preferences');
                        break;
                    case "3":
                        $this->addOrderNote($specialRequestString, 'order_info.expedia.multi_room_booking_info');
                        break;
                    case "4":
                        $this->addOrderNote($specialRequestString, 'order_info.expedia.user_comment');
                        break;
                    case "5":
                        $this->addOrderNote($specialRequestString, 'order_info.expedia.payment_instructions');
                        break;
                    case "6":
                        $this->addOrderNote($specialRequestString, 'order_info.expedia.add_promotion');
                        break;
                }
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

    public function getSource(): ?PackageSource
    {
        if (!$this->isSourceInit) {
            $this->source = $this->dm->getRepository('MBHPackageBundle:PackageSource')
                ->findOneBy(['code' => $this->getChannelManagerName()]);

            $this->isSourceInit = true;
        }

        return $this->source;
    }

    /**
     * @return string
     */
    public function getChannelManagerName(): string
    {
        $sourceString = (string)$this->getCommonOrderData('source');

        return self::removeChannelManagerNamePrefix($sourceString);
    }

    /**
     * @param $sourceString
     * @return string
     */
    public static function removeChannelManagerNamePrefix($sourceString)
    {
        $prefix = 'A-';
        $prefixPosition = strpos($sourceString, $prefix);
        $requestorName = $prefixPosition === false ? $sourceString : substr($sourceString, strlen($prefix));

        return strtolower($requestorName);
    }
}