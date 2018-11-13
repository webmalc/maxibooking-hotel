<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\BookingRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Service;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerServiceInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerOverview;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 *  ChannelManager service
 */
class Booking extends Base implements ChannelManagerServiceInterface
{
    const UNAVAIBLE_PRICES = [
        'isPersonPrice' => false,
        'additionalChildrenPrice' => null,
        'additionalPrice' => null,
    ];

    const UNAVAIBLE_RESTRICTIONS = [
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
    ];

    /**
     * Config class
     */
    const CONFIG = 'BookingConfig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://supply-xml.booking.com/hotels/xml/';

    /**
     * Base secure API URL
     */
    const BASE_SECURE_URL = 'https://secure-supply-xml.booking.com/hotels/xml/';

    public $servicesConfig = [
        1 => 'Breakfast',
        2 => 'Continental breakfast',
        3 => 'American breakfast',
        4 => 'Buffet breakfast',
        5 => 'Full english breakfast',
        6 => 'Lunch',
        7 => 'Dinner',
        8 => 'Half board',
        9 => 'Full board',
        11 => 'Breakfast for Children',
        12 => 'Continental breakfast for Children',
        13 => 'American breakfast for Children',
        14 => 'Buffet breakfast for Children',
        15 => 'Full english breakfast for Children',
        16 => 'Lunch for Children',
        17 => 'Dinner for Children',
        18 => 'Half board for Children',
        19 => 'Full board for Children',
        20 => 'WiFi',
        21 => 'Internet',
        22 => 'Parking space',
        23 => 'Extrabed',
        24 => 'Babycot'
    ];

    /**
     * @var array
     */
    private $params;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['booking'];
    }

    // /**
    //  * {{ @inheritDoc }}
    //  */
    // public function getOverview(\DateTime $begin, \DateTime $end, Hotel $hotel): ?ChannelManagerOverview
    // {
    //     return null;
    // }

    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $tariffs = [];
        foreach ($this->pullTariffs($config) as $key => $tariff) {
            if (!$tariff['readonly'] || !$tariff['is_child_rate']) {
                $tariffs[$key] = $tariff;
            }
        }
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Booking:close.xml.twig',
            [
                'config' => $config,
                'params' => $this->params,
                'rooms' => $this->pullRooms($config),
                'rates' => $tariffs
            ]
        );
        $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);

        $this->log($sendResult);

        return $this->checkResponse($sendResult);
    }

    /**
     * {@inheritDoc}
     * @throws \Throwable
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Booking:get.xml.twig',
            ['config' => $config, 'params' => $this->params]
        );

        $response = $this->sendXml(static::BASE_URL . 'roomrates', $request);

        if (!$this->hasErrorNode($response)) {
            foreach ($response->room as $room) {
                foreach ($room->rates->rate as $rate) {
                    if (isset($result[(string)$rate['id']]['rooms'])) {
                        $rooms = $result[(string)$rate['id']]['rooms'];
                    } else {
                        $rooms = [];
                    }
                    $rooms[(string)$room['id']] = (string)$room['id'];

                    $result[(string)$rate['id']] = [
                        'title' => (string)$rate['rate_name'],
                        'readonly' => empty((int)$rate['readonly']) ? false : true,
                        'is_child_rate' => empty((int)$rate['is_child_rate']) ? false : true,
                        'rooms' => $rooms
                    ];
                }
            }
        } else {
            $this->log($response->asXML());
            $this->notifyErrorRequest(
                'Booking.com',
                'channelManager.commonCM.notification.request_error.pull_tariffs'
            );
        }

        return $result;
    }

    /**
     * {@ inheritDoc}
     * @throws \Throwable
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Booking:get.xml.twig',
            ['config' => $config, 'params' => $this->params]
        );

        $response = $this->sendXml(static::BASE_URL . 'rooms', $request);
        if (!$this->hasErrorNode($response)) {
            foreach ($response->xpath('room') as $room) {
                $result[(string)$room['id']] = (string)$room;
            }
        } else {
            $this->log($response->asXML());
            $this->notifyErrorRequest(
                'Booking.com',
                'channelManager.commonCM.notification.request_error.pull_rooms'
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }
        $xml = simplexml_load_string($response);
        if ($this->hasErrorNode($xml)) {
            $this->addError($response);
            return false;
        }

        return count($xml->xpath('/'. ($params['element'] ?? 'ok'))) ? true : false;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return bool
     */
    private function hasErrorNode(\SimpleXMLElement $xml)
    {
        return count($xml->xpath('error')) || count($xml->xpath('fault'));
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                            'roomstosell' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0
                        ];
                    } else {
                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                            'roomstosell' => 0
                        ];
                    }
                }
            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Booking:updateRooms.xml.twig',
                [
                    'config' => $config,
                    'params' => $this->params,
                    'data' => $data,
                ]
            );
            $sendResult = $this->send(static::BASE_URL.'availability', $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var BookingConfig $config */
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config, true);
            $serviceTariffs = $this->pullTariffs($config);
            $priceCachesCallback = function () use ($begin, $end, $config, $roomType) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $config->getHotel(),
                    $this->getRoomTypeArray($roomType),
                    [],
                    true,
                    $this->roomManager->useCategories
                );
            };
            $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                $roomTypeId = $this->getRoomTypeArray($roomTypeInfo['doc'])[0];
                $bookingRoom = $config->getRoomById($roomTypeInfo['syncId']);

                /** @var \DateTime $day */
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    foreach ($tariffs as $tariff) {
                        /** @var Tariff $tariffDocument */
                        $tariffDocument = $tariff['doc'];
                        $tariffId = $tariffDocument->getId();
                        $tariffChildOptions = $tariffDocument->getChildOptions();
                        //Если тариф дочерний, берем данные о ценах по id родительского тарифа.
                        $syncPricesTariffId = ($tariffDocument->getParent() && $tariffChildOptions->isInheritPrices())
                            ? $tariffDocument->getParent()->getId()
                            : $tariffId;

                        if (!isset($serviceTariffs[$tariff['syncId']]) || $serviceTariffs[$tariff['syncId']]['readonly'] || $serviceTariffs[$tariff['syncId']]['is_child_rate']) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'], $serviceTariffs[$tariff['syncId']]['rooms'])) {
                            continue;
                        }

                        if (isset($priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')])) {
                            /** @var PriceCache $info */
                            $info = $priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')];
                            $calculator = $this->container->get('mbh.calculation');
                            $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                'price' => $this->currencyConvertFromRub($config, $calculator->getPriceWithTariffPromotionDiscount($info->getPrice(), $info->getTariff())),
                                'price1' => $info->getSinglePrice() && (!($bookingRoom instanceOf BookingRoom) || $bookingRoom->isUploadSinglePrices())
                                    ? $this->currencyConvertFromRub($config, $calculator->getPriceWithTariffPromotionDiscount($info->getSinglePrice(), $info->getTariff()))
                                    : null,
                                'closed' => false
                            ];
                        } else {
                            $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                'price' => null,
                                'price1' => null,
                                'closed' => true
                            ];
                        }
                    }
                }
            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Booking:updatePrices.xml.twig',
                [
                    'config' => $config,
                    'params' => $this->params,
                    'data' => $data,
                    'begin' => $begin,
                    'end' => $end
                ]
            );

            $sendResult = $this->send(static::BASE_URL.'availability', $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config, true);
            $serviceTariffs = $this->pullTariffs($config);
            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                [],
                true
            );
            $priceCachesCallback = function () use ($begin, $end, $config, $roomType) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $config->getHotel(),
                    $roomType ? [$roomType->getId()] : [],
                    [],
                    true
                );
            };
            $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    foreach ($tariffs as $tariff) {
                        /** @var Tariff $tariffDocument */
                        $tariffDocument = $tariff['doc'];
                        $tariffId = $tariffDocument->getId();
                        $tariffChildOptions = $tariffDocument->getChildOptions();
                        //Если тариф дочерний, берем данные о ценах по id родительского тарифа.
                        $syncPricesTariffId = ($tariffDocument->getParent() && $tariffChildOptions->isInheritPrices())
                            ? $tariffDocument->getParent()->getId()
                            : $tariffId;
                        $syncRestrictionsTariffId = ($tariffDocument->getParent() && $tariffChildOptions->isInheritRestrictions())
                            ? $tariffDocument->getParent()->getId()
                            : $tariffId;

                        if (!isset($serviceTariffs[$tariff['syncId']]) || $serviceTariffs[$tariff['syncId']]['readonly'] || $serviceTariffs[$tariff['syncId']]['is_child_rate']) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'], $serviceTariffs[$tariff['syncId']]['rooms'])) {
                            continue;
                        }

                        $price = false;
                        if (isset($priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')])) {
                            $price = true;
                        }

                        if (isset($restrictions[$roomTypeId][$syncRestrictionsTariffId][$day->format('d.m.Y')])) {
                            $info = $restrictions[$roomTypeId][$syncRestrictionsTariffId][$day->format('d.m.Y')];
                            $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                'minimumstay_arrival' => (int)$info->getMinStayArrival(),
                                'maximumstay_arrival' => (int)$info->getMaxStayArrival(),
                                'minimumstay' => (int)$info->getMinStay(),
                                'maximumstay' => (int)$info->getMaxStay(),
                                'closedonarrival' => $info->getClosedOnArrival() ? 1 : 0,
                                'closedondeparture' => $info->getClosedOnDeparture() ? 1 : 0,
                                'closed' => $info->getClosed() || !$price ? 1 : 0,
                            ];
                        } else {
                            $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                'minimumstay_arrival' => 0,
                                'maximumstay_arrival' => 0,
                                'minimumstay' => 0,
                                'maximumstay' => 0,
                                'closedonarrival' => 0,
                                'closedondeparture' => 0,
                                'closed' => !$price ? 1 : 0,
                            ];
                        }
                    }
                }
            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Booking:updateRestrictions.xml.twig',
                [
                    'config' => $config,
                    'params' => $this->params,
                    'data' => $data,
                    'begin' => $begin,
                    'end' => $end
                ]
            );
            $sendResult = $this->send(static::BASE_URL.'availability', $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function createPackages()
    {
        return $this->pullOrders();
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function pullOrders($pullOldStatus = ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS)
    {
        $result = true;
        $isPulledAllPackages = $pullOldStatus === ChannelManager::OLD_PACKAGES_PULLING_ALL_STATUS;
        /** @var BookingConfig $config */
        foreach ($this->getConfig($isPulledAllPackages) as $config) {
            $sendResult = $this->sendPullOrdersRequest($pullOldStatus, $config, $isPulledAllPackages);
            $this->log('Reservations: ' . $sendResult->asXml());

            if (!$this->checkResponse($sendResult->asXml(), ['element' => 'reservations'])) {
                return false;
            };

            foreach ($sendResult->reservation as $reservation) {
                if ((string)$reservation->status == 'modified' || $isPulledAllPackages) {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => (string)$reservation->id,
                        'channelManagerType' => 'booking'
                    ]
                );
                if ((string)$reservation->status == 'modified' || $isPulledAllPackages) {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                if (((string)$reservation->status == 'new' && !$order) || ($isPulledAllPackages && !$order)) {
                    $result = $this->createPackage($reservation, $config);
                    if ($isPulledAllPackages) {
                        $result->setConfirmed(true);
                        $this->dm->flush();
                    }
                    $this->notify($result, 'booking', 'new');
                }
                //edit
                if ((string)$reservation->status == 'modified') {
                    $result = $this->createPackage($reservation, $config, $order);
                    $this->notify($result, 'booking', 'edit');
                }
                //delete
                if ((string)$reservation->status == 'cancelled' && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, 'booking', 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (in_array((string)$reservation->status, ['modified', 'cancelled']) && !$order) {
                    $this->notifyError(
                        'booking',
                        '#' . $reservation->id . ' ' .
                        $reservation->customer->last_name . ' ' . $reservation->customer->first_name
                    );
                }
            };
            if ($result && $isPulledAllPackages) {
                $config->setIsAllPackagesPulled(true);
                $this->dm->flush();
            }
        }

        if ($isPulledAllPackages) {
            $cm = $this->container->get('mbh.channelmanager');
            $cm->clearAllConfigsInBackground();
            $cm->updateInBackground();
        }

        return $result;
    }

    /**
     * @param \SimpleXMLElement $reservation
     * @param ChannelManagerConfigInterface $config
     * @param Order $order
     * @return Order
     */
    private function createPackage(
        \SimpleXMLElement $reservation,
        ChannelManagerConfigInterface $config,
        Order $order = null
    ) {
    
        $helper = $this->container->get('mbh.helper');
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);
        $services = $this->getServices($config);

        //tourist
        $customer = $reservation->customer;

        $payerNote = 'country=' . (string)$customer->countrycode;
        $payerNote .= '; city=' . (string)$customer->city;
        $payerNote .= '; zip=' . (string)$customer->zip;
        $payerNote .= '; company=' . (string)$customer->company;
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$customer->last_name,
            (string)$customer->first_name,
            null,
            null,
            empty((string)$customer->email) ? null : (string)$customer->email,
            empty((string)$customer->telephone) ? null : (string)$customer->telephone,
            empty((string)$customer->address) ? null : (string)$customer->address,
            empty($payerNote) ? null : $payerNote
        );
        //order
        if (!$order) {
            $order = new Order();
            $order->setChannelManagerStatus('new');
        } else {
            foreach ($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setDeletedAt(null);
        }

        $orderPrice = $this->currencyConvertToRub($config, (float)$reservation->totalprice);

        $order->setChannelManagerType('booking')
            ->setChannelManagerId((string)$reservation->id)
            ->setChannelManagerHumanId(empty((string)$customer->loyalty_id) ? null : (string)$customer->loyalty_id)
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderPrice)
            ->setOriginalPrice((float)$reservation->totalprice)
            ->setTotalOverwrite($orderPrice)
            ->setNote('remarks=' . (string)$customer->remarks);

        if (!empty((string)$customer->cc_number)) {
            $card = new CreditCard();
            $card->setType($customer->cc_type)
                ->setNumber($customer->cc_number)
                ->setDate($customer->cc_expiration_date)
                ->setCardholder($customer->cc_name)
                ->setCvc($customer->cc_cvc);

            $order->setCreditCard($card);
        }
        $this->dm->persist($order);
        $this->dm->flush();

        $cashDocuments = [];
        if (!empty((string)$reservation->reservation_extra_info)
            && !empty((string)$reservation->reservation_extra_info->payer)
            && !empty((string)$reservation->reservation_extra_info->payer->payments)) {
            foreach ($reservation->reservation_extra_info->payer->payments->payment as $paymentNode) {
                $attributes = $paymentNode->attributes();
                $note = (isset($attributes['payment_type']) ? ('payment_type:' . (string)$attributes['payment_type']) : '')
                    . (isset($attributes['payout_type']) ? (' payout_type:' . (string)$attributes['payout_type']) : '');

                $cashDocuments[] = (new CashDocument())
                    ->setMethod(CashDocument::METHOD_ELECTRONIC)
                    ->setOperation(CashDocument::OPERATION_IN)
                    ->setOrder($order)
                    ->setTouristPayer($payer)
                    ->setTotal($this->currencyConvertToRub($config, (float)$attributes['amount']))
                    ->setNote($note)
                    ->setIsConfirmed(false);
            }
        }

        //fee
        if (!empty((float)$reservation->commissionamount)) {
            $cashDocuments[] = (new CashDocument())
                ->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod(CashDocument::METHOD_ELECTRONIC)
                ->setOperation(CashDocument::OPERATION_FEE)
                ->setOrder($order)
                ->setTouristPayer($payer)
                ->setTotal($this->currencyConvertToRub($config, (float)$reservation->commissionamount));
        }
        $this->container->get('mbh.channelmanager.order_handler')->saveCashDocuments($order, $cashDocuments);
        $this->dm->flush();

        //packages
        foreach ($reservation->room as $room) {
            $corrupted = false;
            $errorMessage = '';

            //roomType
            if (isset($roomTypes[(string)$room->id])) {
                $roomType = $roomTypes[(string)$room->id]['doc'];
            } else {
                $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                    [
                        'hotel.id' => $config->getHotel()->getId(),
                        'isEnabled' => true,
                        'deletedAt' => null
                    ]
                );
                $corrupted = true;
                $errorMessage = 'ERROR: invalid roomType #' . (string)$room->id . '. ';

                if (!$roomType) {
                    continue;
                }
            }

            //guests
            if ($payer->getFirstName() . ' ' . $payer->getLastName() == (string)$room->guest_name) {
                $guest = $payer;
            } else {
                $guest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    'н/д',
                    (string)$room->guest_name
                );
            }

            //prices
            $total = 0;
            $tariff = $rateId = null;
            $packagePrices = [];
            foreach ($room->price as $price) {
                if (!$rateId) {
                    $rateId = (string)$price['rate_id'];
                }
                if (!$tariff && isset($tariffs[$rateId])) {
                    $tariff = $tariffs[$rateId]['doc'];
                }
                if (!$tariff) {
                    $tariff = $this->createTariff($config, $rateId);

                    if (!$tariff) {
                        continue;
                    }
                    $corrupted = true;
                    $errorMessage .= 'ERROR: Not mapped rate <' . $tariff->getName() . '>. ';
                }
                $total += (float)$price;
                $date = $helper->getDateFromString((string)$price['date'], 'Y-m-d');
                $packagePrices[] = new PackagePrice($date, $this->currencyConvertToRub($config, (float)$price), $tariff);
            }


            $packageNote = 'remarks: ' . $room->remarks . '; extra_info: ' . $room->extra_info . '; facilities: ' . $room->facilities . '; max_children: ' . $room->max_children;
            $packageNote .= '; commissionamount=' . $room->commissionamount . '; currencycode = ' . $room->currencycode . '; ';
            $packageNote .= $errorMessage;

            $packageTotal = $this->currencyConvertToRub($config, (float)$total);
            $package = new Package();
            $package
                ->setChannelManagerId((string)$room->roomreservation_id)
                ->setChannelManagerType('booking')
                ->setBegin($helper->getDateFromString((string)$room->arrival_date, 'Y-m-d'))
                ->setEnd($helper->getDateFromString((string)$room->departure_date, 'Y-m-d'))
                ->setRoomType($roomType)
                ->setTariff($tariff)
                ->setAdults((int)$room->numberofguests)
                ->setChildren(0)
                ->setIsSmoking((int)$room->smoking ? true : false)
                ->setPrices($packagePrices)
                ->setPrice($packageTotal)
                ->setOriginalPrice((float)$total)
                ->setTotalOverwrite($packageTotal)
                ->setNote($packageNote)
                ->setOrder($order)
                ->setCorrupted($corrupted)
                ->addTourist($guest);

            //services
            $servicesTotal = 0;

            if ($room->addons->addon) {
                foreach ($room->addons->addon as $addon) {
                    $servicesTotal += (float)$addon->totalprice;
                    if (empty($services[(int)$addon->type])) {
                        continue;
                    }

                    $packageService = new PackageService();
                    $packageService
                        ->setService($services[(int)$addon->type]['doc'])
                        ->setIsCustomPrice(true)
                        ->setNights(empty((string)$addon->nights) ? null : (int)$addon->nights)
                        ->setPersons(empty((string)$addon->persons) ? null : (int)$addon->persons)
                        ->setPrice(
                            empty((string)$addon->price_per_unit) ? null : $this->currencyConvertToRub(
                                $config,
                                (float)$addon->price_per_unit
                            )
                        )
                        ->setTotalOverwrite($this->currencyConvertToRub($config, (float)$addon->totalprice))
                        ->setPackage($package);
                    $this->dm->persist($packageService);
                    $package->addService($packageService);
                }
            }

            $package->setServicesPrice($this->currencyConvertToRub($config, (float)$servicesTotal));
            $package->setTotalOverwrite($this->currencyConvertToRub($config, (float)$room->totalprice));

            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->persist($order);
            $this->dm->flush();
        }
        $order->setTotalOverwrite($this->currencyConvertToRub($config, (float)$reservation->totalprice));
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     */
    public function syncServices(ChannelManagerConfigInterface $config)
    {
        $config->removeAllServices();
        foreach ($this->servicesConfig as $serviceKey => $serviceName) {
            $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy(
                [
                    'code' => $serviceName
                ]
            );

            if (empty($serviceDoc) || $serviceDoc->getCategory()->getHotel()->getId() != $config->getHotel()->getId()) {
                continue;
            }

            $service = new Service();
            $service->setServiceId($serviceKey)->setService($serviceDoc);
            $config->addService($service);
            $this->dm->persist($config);
        }

        $this->dm->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
    }

    /**
     * @param BookingConfig $config
     * @return bool
     * @throws \Exception
     */
    public function isBookingAccountConfirmed(BookingConfig $config)
    {
        $response = $this->sendPullOrdersRequest(ChannelManager::OLD_PACKAGES_PULLING_ALL_STATUS, $config, true);
        if ($this->hasErrorNode($response)) {
            if ($response->fault && $response->fault->attributes()['code'] == '403') {
                return false;
            } else {
                throw new \Exception('Unexpected error while sending request for check booking confirmation');
            }
        }

        return true;
    }

    /**
     * @param $pullOldStatus
     * @param $config
     * @param $isPulledAllPackages
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    private function sendPullOrdersRequest($pullOldStatus, $config, $isPulledAllPackages): \SimpleXMLElement
    {
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Booking:reservations.xml.twig',
            ['config' => $config, 'params' => $this->params, 'pullOldStatus' => $pullOldStatus]
        );

        $endpointUrl = static::BASE_SECURE_URL . ($isPulledAllPackages ? 'reservationssummary' : 'reservations');

        $sendResult = $this->sendXml($endpointUrl, $request, null, true);

        return $sendResult;
    }
}
