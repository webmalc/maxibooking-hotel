<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractOrderInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerException;
use MBH\Bundle\ChannelManagerBundle\Services\OrderHandler;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    private $currency;
    /** @var  OrderHandler $orderHandler */
    private $orderHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->orderHandler = $this->container->get('mbh.channel_manager.order_handler');
    }

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
        $bookingSession,
        $currency
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
        $this->currency = $currency;

        return $this;
    }

    public function getPayer(): Tourist
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
        $payer->setPhone($phoneNumber, false);
        $this->dm->flush();

        return $payer;
    }

    public function getChannelManagerOrderId(): string
    {
        return (string)$this->bookingSession;
    }

    public function getPrice()
    {
        return $this->getPriceAtBooking() + $this->getPriceAtCheckOut();
    }

    private function getPriceAtBooking()
    {
        $priceInCurrency = (float)$this->finalPriceAtBooking['amount'];

        return $this->container->get('mbh.currency')->convertToRub($priceInCurrency, $this->currency);
    }

    private function getPriceAtCheckOut()
    {
        $priceInCurrency = (float)$this->finalPriceAtCheckout['amount'];

        return $this->container->get('mbh.currency')->convertToRub($priceInCurrency, $this->currency);
    }

    public function getCashDocuments(Order $order)
    {
        $cashDocuments = [];
        if ($this->getPriceAtBooking() > 0) {
            $cashDocuments[] = (new CashDocument())
                ->setIsConfirmed(false)
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal($this->getPriceAtBooking());
        }

        if ($this->getPriceAtCheckOut() > 0) {
            $cashDocuments[] = (new CashDocument())
                ->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod('cash')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($this->getPayer())
                ->setTotal($this->getPriceAtCheckOut());
        }

        return $cashDocuments;
    }

    public function getSource(): ?PackageSource
    {
        return $this->dm->getRepository('MBHPackageBundle:PackageSource')->findOneBy(['code' => $this->getChannelManagerName()]);
    }

    /**
     * Возвращает массив объектов, хранящих данные о бронях в заказе
     * @return AbstractPackageInfo[]
     */
    public function getPackagesData()
    {
        $tariff = $this->dm->find('MBHPriceBundle:Tariff', $this->bookingMainData['tariffId']);
        $roomType = $this->getRoomType();

        $packagesData = [];
        foreach ($this->roomsData as $roomData) {
            $adultsChildrenCount = $this->orderHandler->getAdultsChildrenCounts($roomData['party'], $tariff);
            $adultsChildrenCombinations = $this->orderHandler->getDividedAdultsChildrenCombinations($adultsChildrenCount['adultsCount'],
                $adultsChildrenCount['childrenCount'], $roomType->getTotalPlaces());
            foreach ($adultsChildrenCombinations as $iterationIndex => $combination) {
                if ($iterationIndex == 0) {
                    $childrenAges = $roomData['party']['children'];
                    $travellerData = $roomData;
                } else {
                    $childrenAges = [];
                    $travellerData = [];
                }

                $childrenCount = isset($combination['children']) ? $combination['children'] : 0;
                $packagesData[] = $this->container->get('mbh.channel_manager.trip_advisor_package_info')
                    ->setInitData($this->checkInDate, $this->checkOutDate, $this->bookingMainData,
                        $this->bookingSession, $this->getPayer(), $tariff, $roomType, $childrenAges,
                        $childrenCount, $combination['adults'], $travellerData);
            }
        }

        return $packagesData;
    }

    private function getRoomType(): RoomType
    {
        $roomTypeId = $this->bookingMainData['roomTypeId'];
        $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
        if (!$roomType) {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $this->bookingMainData['hotelId'],
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $this->addProblemMessage('services.expedia.invalid_room_type_id');
        }

        if (!$roomType) {
            throw new ChannelManagerException($this->translator->trans('services.expedia.nor_one_room_type'));
        }

        return $roomType;
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

    public function getChannelManagerName(): string
    {
        return 'tripadvisor';
    }

    public function getChannelManagerDisplayedName(): string
    {
        return $this->getChannelManagerName();
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
    public function isHandleAsNew(?Order $order): bool
    {
        return true;
    }

    /**
     * Обрабатывать ли данный заказ как измененный?
     * @param Order $order
     * @return bool
     */
    public function isHandleAsModified(?Order $order): bool
    {
        return false;
    }

    /**
     * Обрабатывать ли данный заказ как законченный
     * @param Order $order
     * @return bool
     */
    public function isHandleAsCancelled(?Order $order): bool
    {
        return false;
    }

    public function getNote(): string
    {
        return $this->specialRequests ? $this->specialRequests : '';
    }
}