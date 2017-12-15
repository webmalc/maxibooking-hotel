<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;

class ExpediaNotificationOrderInfo extends AbstractOrderInfo
{
    const NEW_ORDER_NODE_NAME = 'OTA_HotelResNotifRQ';
    const MODIFIED_ORDER_NODE_NAME = 'OTA_HotelResModifyNotifRQ';
    const CANCELLED_ORDER_NODE_NAME = 'OTA_CancelRQ';

    /** @var  \SimpleXMLElement */
    private $notificationElement;
    /** @var  \SimpleXMLElement */
    private $orderInfoElement;
    /** @var  \SimpleXMLElement */
    private $roomStayElement;
    /** @var  ExpediaConfig */
    private $config;
    private $tariffs;
    private $roomTypes;
    private $requestorId;
    private $isSmoking = false;

    private $isPackagesDataInit = false;
    private $packagesData = [];
    private $payer;
    private $isPayerInit = false;
    private $source;
    private $isSourceInit = false;

    public function setInitData(\SimpleXMLElement $notificationElement, ExpediaConfig $config, $tariffs, $roomTypes)
    {
        $this->notificationElement = $notificationElement;
        if ($this->isOrderCreated()) {
            $this->orderInfoElement = $notificationElement->Body->OTA_HotelResNotifRQ->HotelReservations->HotelReservation;
            $this->requestorId = (string)$notificationElement->Body->OTA_HotelResNotifRQ->POS->Source->RequestorID->attributes()['ID'];
        } elseif ($this->isOrderModified()) {
            $this->orderInfoElement = $notificationElement->Body->OTA_HotelResModifyNotifRQ->HotelResModifies->HotelResModify;
            $this->requestorId = (string)$notificationElement->Body->OTA_HotelResModifyNotifRQ->POS->Source->RequestorID->attributes()['ID'];
        } else {
            $this->orderInfoElement = $notificationElement->Body->OTA_CancelRQ;
            $this->requestorId = (string)$notificationElement->Body->OTA_CancelRQ7->POS->Source->RequestorID->attributes()['ID'];
        }

        $this->roomStayElement = $this->orderInfoElement->RoomStays->RoomStay;
        $this->config = $config;
        $this->tariffs = $tariffs;
        $this->roomTypes = $roomTypes;
        $this->handleSpecialRequests();

        return $this;
    }

    public function getPayer(): Tourist
    {
        if (!$this->isPayerInit) {
            /** @var \SimpleXMLElement $customerNode */
            $customerNode = $this->orderInfoElement->ResGuests->ResGuest->Profiles->ProfileInfo->Profile->Customer;
            $nameNode = $customerNode->PersonName[0];
            $lastName = trim((string)$nameNode->Surname);
            $firstName = trim((string)$nameNode->GivenName);
            $patronymic = trim((string)$nameNode->MiddleName) ?? null;
            $phoneNumber = null;
            if (!empty($customerNode->Telephone->attributes())) {
                $phoneAttributes = $customerNode->Telephone->attributes();
                $phoneNumber = '';

                !isset($phoneAttributes['CountryAccessCode']) ?: $phoneNumber .= $phoneAttributes['CountryAccessCode'];
                !isset($phoneAttributes['AreaCityCode']) ?: $phoneNumber .= $phoneAttributes['AreaCityCode'];
                !isset($phoneAttributes['PhoneNumber']) ?: $phoneNumber .= $phoneAttributes['PhoneNumber'];
            }

            $this->payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                $lastName,
                $firstName,
                $patronymic,
                null,
                null,
                $phoneNumber == '' ? null : $phoneNumber
            );

            $this->isPayerInit = true;
        }

        return $this->payer;
    }

    public function getChannelManagerOrderId(): string
    {
        foreach ($this->orderInfoElement->UniqueID as $uniqueIdNode) {
            if ((string)$uniqueIdNode->attributes()['Type'] == "14") {
                return (string)$this->orderInfoElement->UniqueID->attributes()['ID'];
            }
        }

        throw new \Exception('Channel manager id not specified');
    }

    public function getPrice()
    {
        return current($this->getPackagesData())->getPrice();
    }

    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        /** @var \SimpleXMLElement $pricesNode */
        $pricesNode = $this->roomStayElement->Total;
        $sumOfTaxes = 0;
        if (!is_null($this->getCreditCard()) && !($this->requestorId[0] == 'A')) {
            /** @var \SimpleXMLElement $taxNode */
            foreach ($pricesNode->Taxes as $taxNode) {
                $taxCashDocument = new CashDocument();
                $taxSum = (float)$taxNode->attributes()['Amount'];
                $sumOfTaxes += $taxSum;
                if ($taxSum > 0) {
                    $cashDocuments[] = $taxCashDocument
                        ->setIsPaid(true)
                        ->setMethod('electronic')
                        ->setOperation('out')
                        ->setOrder($order)
                        ->setTouristPayer($this->getPayer())
                        ->setTotal($taxSum);

                }
            }

            $cashDocument = new CashDocument();
            $cashDocuments[] = $cashDocument
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal((float)$pricesNode->attributes()['AmountAfterTax']);
        }

        return $cashDocuments;
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
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        if (!$this->isPackagesDataInit) {
            //Создаем и добавляем объект, хранящий данные о брони.
            // Добавляется один, так как в принимаемых из expedia заказах содержится только 1 бронь
            $this->packagesData[] = $this->container->get('mbh.channel_manager.expedia_notification_package_info')
                ->setInitData($this->roomStayElement, $this->config, $this->tariffs, $this->roomTypes, $this->getPayer())
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
        return $this->isSmoking;
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
        $cardNode = $this->roomStayElement->Guarantee;
        if (!empty((string)$cardNode)) {
            /** @var \SimpleXMLElement $cardDataNode */
            $cardDataNode = $cardNode[0]->GuaranteesAccepted->GuaranteeAccepted->PaymentCard;
            $cardDataNodeAttributes = $cardDataNode->attributes();
            $card = new CreditCard();

            $card->setNumber($cardDataNodeAttributes['CardNumber'])
                ->setType($this->getCardTypeByAbbreviation((string)$cardDataNodeAttributes['CardCode']))
                ->setDate($cardDataNodeAttributes['ExpireDate'])
                ->setCardholder((string)$cardDataNode->CardHolderName);

            if (!empty((string)$cardDataNodeAttributes['SeriesCode'])) {
                $card->setCvc($cardDataNodeAttributes['SeriesCode']);
            }
        }

        return $card;
    }

    /**
     * @return string
     */
    public function getChannelManagerName(): string
    {
        $prefix = 'A-';
        $prefixPosition = strpos($this->requestorId, $prefix);
        $requestorName = $prefixPosition === false ? $this->requestorId : substr($this->requestorId, strlen($prefix));

        if (in_array($requestorName, ['Hotels', 'Venere'])) {
            return strtolower($requestorName);
        }

        return 'expedia';
    }

    public function isOrderModified(): bool
    {
        return $this->getOrderDataNodeName() == self::MODIFIED_ORDER_NODE_NAME;
    }

    public function isOrderCreated(): bool
    {
        return $this->getOrderDataNodeName() == self::NEW_ORDER_NODE_NAME;
    }

    public function isOrderCancelled(): bool
    {
        return $this->getOrderDataNodeName() == self::CANCELLED_ORDER_NODE_NAME;
    }

    /**
     * Обрабатывать ли данный заказ как новый?
     * @param Order $order
     * @return bool
     */
    public function isHandledAsNew(?Order $order): bool
    {
        return $this->isOrderCreated() && !$order;
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
     * @return string
     */
    public function getNote(): string
    {
        return $this->orderNote;
    }

    /**
     * @param $cardAbbreviation
     * @return string
     */
    private function getCardTypeByAbbreviation($cardAbbreviation)
    {
        $cardAbbreviationToFullNameRelations = [
            'AX' => 'American Express',
            'DN' => 'Diners Club',
            'DS' => 'Discover Card',
            'JC' => 'JCB International',
            'MC' => 'MasterCard',
            'VI' => 'Visa'
        ];

        return $cardAbbreviationToFullNameRelations[$cardAbbreviation];
    }

    private function getOrderDataNodeName()
    {
        return (string)$this->notificationElement->Header->Interface->PayloadInfo->PayloadDescriptor->attributes()['Name'];
    }


    private function handleSpecialRequests(): void
    {
        if (!empty((string)$this->roomStayElement->SpecialRequests)) {
            foreach ($this->roomStayElement->SpecialRequests->SpecialRequest as $specialRequest) {
                $preface = '';
                $codeString = (string)$specialRequest->attributes()['RequestCode'][0];
                $specialRequestString = (string)$specialRequest->Text;
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
                        $preface = 'order_info.expedia.bedding_preferences';
                        break;
                    case "2":
                        $this->isSmoking = $specialRequestString === 'Smoking';
                        break;
                    case "3":
                        $preface = 'order_info.expedia.multi_room_booking_info';
                        break;
                    case "4":
                        $preface = 'order_info.expedia.user_comment';
                        break;
                    case "5":
                        $preface = 'order_info.expedia.payment_instructions';
                        break;
                    case "6":
                        $preface = 'order_info.expedia.add_promotion';
                        break;
                }

                if (!empty($preface)) {
                    $this->addOrderNote($specialRequestString, $preface);
                }
            }
        }
    }
}