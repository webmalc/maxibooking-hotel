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
    /** @var  \SimpleXMLElement */
    private $notificationElement;
    /** @var  \SimpleXMLElement */
    private $orderInfoElement;
    /** @var  \SimpleXMLElement */
    private $roomStayElement;
    /** @var  ExpediaConfig */
    private $config;

    private $isPackagesDataInit = false;
    private $packagesData = [];
    private $payer;
    private $isPayerInit = false;

    public function setInitData(\SimpleXMLElement $notificationElement, ExpediaConfig $config)
    {
        $this->notificationElement = $notificationElement;
        $this->orderInfoElement = $notificationElement->Body->OTA_HotelResNotifRQ->HotelReservations->HotelReservation;
        $this->roomStayElement = $this->orderInfoElement->RoomStays->RoomStay;
        $this->config = $config;

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
            if (!empty((string)$customerNode->Telephone)) {
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
        return (string)$this->orderInfoElement->UniqueID->attributes()['ID'];
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
        //TODO: Добавить проверку на то, оплачено ли
        /** @var \SimpleXMLElement $taxNode */
        foreach ($pricesNode->Taxes as $taxNode) {
            $taxCashDocument = new CashDocument();
            $taxSum = (float)$taxNode->attributes()['Amount'];
            $sumOfTaxes += $taxSum;
            $cashDocuments[] = $taxCashDocument->setIsConfirmed(false)
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('tax')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal($taxSum);
        }

        $cashDocument = new CashDocument();
        $cashDocuments[] = $cashDocument->setIsConfirmed(false)
            ->setIsPaid(true)
            ->setMethod('electronic')
            ->setOperation('in')
            ->setOrder($order)
            ->setTouristPayer($this->getPayer())
            ->setTotal((float)$pricesNode->attributes()['AmountAfterTax'] + $sumOfTaxes);
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
        $isSmokingElement = $this->roomStayElement->xpath('SpecialRequest[starts-with(@RequestCode, "2")]');
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

    public function getChannelManagerName(): string
    {
        return 'expedia';
    }

    public function isOrderModified(): bool
    {
        return $this->getOrderDataNodeName() == 'OTA_HotelResModifyNotifRQ';
    }

    public function isOrderCreated(): bool
    {
        return $this->getOrderDataNodeName() == 'OTA_HotelResNotifRQ';
    }

    public function isOrderCancelled(): bool
    {
        return $this->getOrderDataNodeName() == 'OTA_CancelRQ';
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

    public function getNote(): string
    {
        if (!empty((string)$this->roomStayElement->SpecialRequest)) {
            foreach ($this->roomStayElement->SpecialRequest as $specialRequest) {
                $codeString = (string)$specialRequest->attributes()['code'][0];
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

        return $this->orderNote;
    }

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
}