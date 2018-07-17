<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\CreditCard;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Service;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

/**
 *  ChannelManager service
 */
class Oktogo extends Base
{
    /**
     * Config class
     */
    const CONFIG = 'OktogoConfig';

    /**
     * Dev or test mode
     */
    const TEST = false;

    /**
     * @var array
     */
    public $servicesConfig = [
        'parking' => 'Parking space',
        'cot' => 'Babycot',
        'CB' => 'Continental breakfast',
        'BB' => 'Breakfast',
        'BR' => 'Full english breakfast',
        'HB' => 'Half board',
        'FB' => 'Full board',
        'AI' => 'All Inclusive',
    ];

    /**
     * Get roomTypes & tariffs template file
     */
    const GET_ROOMS_TARIFFS_TEMPLATE = 'MBHChannelManagerBundle:Oktogo:get.xml.twig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://hotelapi.oktogo.ru/';

    /**
     * @var array
     */
    private $headers = [
        'Content-Type: text/xml; charset=utf-8',
        'Accept: text/xml',
        'Cache-Control: no-cache',
    ];

    /**
     * @var array
     */
    private $params;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['oktogo'];
    }

    /**
     * {@inheritDoc}
     */
    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Oktogo:reservations.xml.twig',
                ['config' => $config]
            );

            $sendResult = $this->sendXml(static::BASE_URL . 'reservations', $request, $this->getHeaders(), true);
            //TEST
//            $sendResult = simplexml_load_string($this->templating->render('MBHChannelManagerBundle:Oktogo:test.xml.twig'));

            $this->log('Reservations count: ' . count($sendResult->reservation));

            foreach ($sendResult->reservation as $reservation) {

                if ((string)$reservation->status == 'modified') {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }

                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => (string)$reservation->id,
                        'channelManagerType' => 'oktogo'
                    ]
                );

                if ((string)$reservation->status == 'modified') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                if ((string)$reservation->status == 'Confirmed' && !$order && !isset($reservation->modification_date)) {

                    $result = $this->createPackage($reservation, $config, $order);
                    $this->notify($result, 'oktogo', 'new');
                }
                //edit
                if ((string)$reservation->status == 'Confirmed' && $order && isset($reservation->modification_date)) {
                    $result = $this->createPackage($reservation, $config, $order);
                    $this->notify($result, 'oktogo', 'edit');
                }
                //delete
                if ((string)$reservation->status == 'Cancelled' && $order && isset($reservation->modification_date)) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, 'oktogo', 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;

                };

            };
        }

        return $result;
    }

    /**
     * @param mixed $response
     * @param array|null $params
     * @return bool
     */
    public function checkResponse($response, array $params = null)
    {

        if (!$response) {
            return false;
        }

        return $response == '<ok />' ? true : false;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @return ChannelManagerConfigInterface
     */
    public function clearConfig(ChannelManagerConfigInterface $config)
    {
        //roomTypes
        $rooms = $this->pullRooms($config);
        foreach ($config->getRooms() as $room) {
            if (!isset($rooms[$room->getRoomId()])) {
                $config->removeRoom($room);
            }
        }
        //tariffs
        $rates = $this->pullTariffs($config);
        foreach ($config->getTariffs() as $tariff) {
            if (!isset($rates[$tariff->getRoomType()][$tariff->getTariffId()])) {
                $config->removeTariff($tariff);
            }
        }

        $this->dm->persist($config);
        $this->dm->flush();

        return $config;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param RoomType|null $roomType
     * @return bool
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);
        $calc = $this->container->get('mbh.calculation');
        // iterate hotels
        foreach ($this->getConfig() as $config) {

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
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    foreach ($tariffs as $tariffId => $tariff) {
                        if (isset($serviceTariffs[$roomTypeInfo['syncId']][$tariff['syncId']])) {
                            if (isset($priceCaches[$roomTypeId][$tariff['doc']->getId()][$day->format('d.m.Y')])) {
                                $info = $priceCaches[$roomTypeId][$tariff['doc']->getId()][$day->format('d.m.Y')];

                                $countPersons = $serviceTariffs[$roomTypeInfo['syncId']][$tariff['syncId']]['persons'];
                                $priceFinal = $calc->calcPrices($info->getRoomType(), $tariff['doc'], $day, $day);

                                $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                    'id_tariff' => $tariff['syncId'],
                                    'price' => isset($priceFinal[$countPersons . '_0']['total']) ? $priceFinal[$countPersons . '_0']['total'] : null,
                                    'persons' => $info->getRoomType()->getPlaces(),
                                ];

                            } else {
                                $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                                    'roomstosell' => 0
                                ];
                            }
                        }


                    }
                }

            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Oktogo:updatePrices.xml.twig',
                [
                    'config' => $config,
                    'data' => $data,
                    'begin' => $begin,
                    'end' => $end
                ]
            );

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, $this->getHeaders(), true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param RoomType|null $roomType
     * @return bool
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

                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {

                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {

                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];

                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                            'roomstosell' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0,
                            'closed' => $info->getIsClosed() > 0 ? 1 : 0
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
                'MBHChannelManagerBundle:Oktogo:updateRooms.xml.twig',
                [
                    'config' => $config,
                    'data' => $data,
                ]
            );

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, $this->getHeaders(), true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @param RoomType|null $roomType
     * @return bool
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
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    foreach ($tariffs as $tariffId => $tariff) {


                        $price = false;
                        if (isset($priceCaches[$roomTypeId][$tariff['doc']->getId()][$day->format('d.m.Y')])) {
                            $price = true;
                        }
                        if (isset($serviceTariffs[$roomTypeInfo['syncId']][$tariff['syncId']])) {
                            if (isset($restrictions[$roomTypeId][$tariff['doc']->getId()][$day->format('d.m.Y')])) {
                                $info = $restrictions[$roomTypeId][$tariff['doc']->getId()][$day->format('d.m.Y')];

                                $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                    'closed' => $info->getClosed() || !$price ? 1 : 0,
                                    'id_tariff' => $tariff['syncId'],
                                    'persons' => $info->getRoomType()->getPlaces(),
                                ];

                            } else {
                                $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')][$tariff['syncId']] = [
                                    'closed' => !$price ? 1 : 0,
                                    'id_tariff' => $tariff['syncId'],
                                    'persons' => $roomTypeInfo['doc']->getPlaces(),
                                ];
                            }
                        }
                    }
                }
            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Oktogo:updateRestrictions.xml.twig',
                [
                    'config' => $config,
                    'data' => $data,
                    'begin' => $begin,
                    'end' => $end
                ]
            );

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, $this->getHeaders(), true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        $auth = '';
        if (static::TEST) {
            $auth = 'Authorization: Basic ' . base64_encode('Oktogo:Oktogo');
        }
        $xAuth = 'X-Oktogo-Authorization: Basic ' . base64_encode($this->params['api_username'] . ':' . $this->params['api_password']);

        return array_merge($this->headers, [$auth, $xAuth]);
    }


    /**
     * @return bool|Order|mixed
     */
    public function createPackages()
    {
        return $this->pullOrders();
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
    )
    {

        $helper = $this->container->get('mbh.helper');
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);
        $services = $this->getServices($config);

        if ($reservation->customer && (string)$reservation->postpay == 'false') {
            $customer = $reservation->customer;
            $payerNote = 'country=' . (string)$customer->countrycode;
            $payerNote .= '; city=' . (string)$customer->city;
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

        } else {
            $payer = null;
        }
        //order
        if (!$order) {
            $order = new Order();
            $order->setChannelManagerStatus('new');
        } else {
            foreach ($order->getPackages() as $package) {
                $this->dm->remove($package);
                $this->dm->flush();
            }
            foreach ($order->getFee() as $cashDoc) {
                $this->dm->remove($cashDoc);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setDeletedAt(null);
        }

        $order->setChannelManagerType('oktogo')
            ->setChannelManagerId((string)$reservation->id)
            ->setChannelManagerHumanId(empty((string)$reservation->id) ? null : (string)$reservation->id)
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice((string)$reservation->totalprice)
            ->setOriginalPrice((float)$reservation->totalprice)
            ->setTotalOverwrite((string)$reservation->totalprice);

        if (!empty((string)$reservation->customer)) {
            $customer = $reservation->customer;
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

        //in
        if (!empty((float)$reservation->totalprice && (string)$reservation->postpay == 'false')) {

            $in = new CashDocument();
            $in->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setTouristPayer($payer)
                ->setTotal((float)$reservation->totalprice);
            $this->dm->persist($in);
            $this->dm->flush();
        }

        //fee
        if (!empty((float)$reservation->commissionamount)) {

            $fee = new CashDocument();
            $fee->setIsConfirmed(false)
                ->setIsPaid(false)
                ->setMethod('electronic')
                ->setOperation('fee')
                ->setOrder($order)
                ->setTouristPayer($payer)
                ->setTotal((float)$reservation->commissionamount);
            $this->dm->persist($fee);
            $this->dm->flush();
        }

        //packages
        foreach ($reservation->room as $room) {

            //packages
            $corrupted = false;
            $errorMessage = '';

            //roomType
            if (isset($roomTypes[(string)$room->id])) {
                $roomType = $roomTypes[(string)$room->id]['doc'];;
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

            $countChildren = 0;
            //guests
            foreach ($room->guest as $guest) {

                if ($guest) {
                    $guests[] = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                        $guest->last_name,
                        $guest->first_name,
                        null,
                        null,
                        null,
                        null
                    );
                    if ((int)$guest->child_age) {
                        $countChildren++;
                    }
                }
            }

            //prices
            $total = 0;
            $tariff = $rateId = null;
            $packagePrices = [];
            foreach ($room->price as $priceItem) {

                $price = $priceItem->attributes();

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

                $total += (float)$priceItem;
                $date = $helper->getDateFromString((string)$price->date, 'Y-m-d');

                $packagePrices[] = new PackagePrice($date, (float)$priceItem, $tariff);
            }

            $packageNote = 'remarks: ' . $room->remarks . 'description: ' . (string)$reservation->special_request->description . '; MealType: ' . $room->meal . '; time = ' . $room->time . '; ' . ' contact_details = ' . $reservation->contract_details . '; ';
            $packageNote .= ' commissionamount=' . $room->commissionamount . '; currencycode = ' . $room->currencycode . '; ' . ' totalprice = ' . $room->totalprice . '; ';
            $packageNote .= $errorMessage;

            if (isset($reservation->special_request)) {
                $special = $reservation->special_request->attributes();
            } else {
                $special['smoking_type'] = '';
            }

            $packageTotal = (float)$total;
            $package = new Package();
            $package
                ->setChannelManagerId((string)$room->roomreservation_id)
                ->setChannelManagerType('oktogo')
                ->setBegin($helper->getDateFromString((string)$room->arrival_date, 'Y-m-d'))
                ->setEnd($helper->getDateFromString((string)$room->departure_date, 'Y-m-d'))
                ->setRoomType($roomType)
                ->setTariff($tariff)
                ->setAdults((int)$room->numberofguests - $countChildren)
                ->setChildren($countChildren)
                ->setIsSmoking(($special['smoking_type'] == 'Smoking') ? true : false)
                ->setPrices($packagePrices)
                ->setPrice($packageTotal)
                ->setOriginalPrice((float)$total)
                ->setTotalOverwrite($packageTotal)
                ->setNote($packageNote)
                ->setOrder($order)
                ->setCorrupted($corrupted);

            foreach ($guests as $key => $tourist) {

                $package->addTourist($tourist);
            }

            //services
            $mealType = (string)$room->meal;
            if (isset($special['parking']) || isset($special['cot']) || isset($special['bed_type']) || isset($mealType)) {

                is_array($special) ? $special[] = $mealType : $special->addAttribute('meal', $mealType);

                foreach ($special as $service => $value) {
                    if (isset($this->servicesConfig[$service]) || isset($this->servicesConfig[(string)$value])) {
                        $packageService = new PackageService();
                        $packageService
                            ->setService(isset($services[$service]['doc']) ? $services[$service]['doc'] : $services[(string)$value]['doc'])
                            ->setIsCustomPrice(true)
                            ->setNights(empty((string)$package->getNights()) ? null : (int)$package->getNights())
                            ->setPersons(null)
                            ->setPrice(null)
                            ->setTotalOverwrite(null)
                            ->setPackage($package);
                        $this->dm->persist($packageService);
                        $package->addService($packageService);
                    }
                }
            }

            $package->setServicesPrice(0);
            $package->setTotalOverwrite(0);

            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->persist($order);
        }

        $this->dm->flush();

        $order->setTotalOverwrite((float)$reservation->totalprice);
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * {@inheritDoc}
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {

        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Oktogo:getRoomsTariffs.xml.twig',
            ['config' => $config]
        );

        $response = $this->sendXml(static::BASE_URL . 'rooms', $request, $this->getHeaders());

        foreach ($response->room as $rooms) {
            $attr = $rooms->attributes();
            $result[(string)$attr['id']] = $attr['room_name'];
        }

        return $result;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Oktogo:getRoomsTariffs.xml.twig',
            ['config' => $config]
        );
        $response = $this->sendXml(static::BASE_URL . 'roomrates', $request, $this->getHeaders());
        foreach ($response->room as $rates) {
            $attrRoom = $rates->attributes();
            foreach ($rates->rates->rate as $rate) {
                $attrRate = $rate->attributes();

                $result[(int)$attrRoom['id']][(int)$attrRate['id']] = [
                    'title' => (string)$rate['rate_name'],
                    'rate_id' => (int)$attrRate['id'],
                    'roomName' => (string)$attrRoom['room_name'],
                    'rooms' => (string)$attrRoom['id'],
                    'persons' => (int)$rate['max_persons']
                ];

            }
        }

        return $result;

    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        foreach ($this->pullTariffs($config) as $key => $tariff) {
            $tariffs[$key] = $tariff;
        }

        $request = $this->templating->render(
            'MBHChannelManagerBundle:Oktogo:close.xml.twig',
            [
                'config' => $config,
                'rooms' => $this->pullRooms($config),
                'rates' => $tariffs
            ]
        );

        $sendResult = $this->send(static::BASE_URL . 'availability', $request, $this->getHeaders(), true);

        $this->log($sendResult);

        return $this->checkResponse($sendResult);
    }

    /**
     * @param Request $request
     * @return \MBH\Bundle\ChannelManagerBundle\Lib\Response
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
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

}