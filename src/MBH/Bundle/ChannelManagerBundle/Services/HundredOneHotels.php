<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Document\Order;

class HundredOneHotels extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'HundredOneHotelsConfig';

    const CHANNEL_MANAGER_TYPE = 'hundredOneHotels';

    const API_KEY = 'ayKWtlRrCobH8ohFFrJO';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://101hotels.info/api2';

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);

        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var HundredOneHotelsConfig $config */
            //$roomTypes array[roomTypeId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //array[roomTypeId][tariffId][date('d.m.Y') => RoomCache]
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );
            $quotasData = [];

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                $currentDateQuotas = [];
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                    $roomQuotaForCurrentDate = 0;
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    }
                    $currentDateQuotas[$roomTypeInfo['syncId']] = $roomQuotaForCurrentDate;
                }
                if (count($currentDateQuotas) > 0) {
                    $quotasData[] = ['day' => $day->format('Y-m-d'), 'quota' => $currentDateQuotas];
                }
            }

            if (!isset($quotasData)) {
                continue;
            }

            $request = $this->getRequestArray($config->getHotelId(), 'set_calendar', $quotasData);
            dump($request);exit();
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

            $result = $this->checkResponse($sendResult);

            $this->log($sendResult);
        }
        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);
        $pricesData = [];
        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var HundredOneHotelsConfig $config */
            //array [maxi TariffId][syncId(service TariffId)=> doc(maxi Tariff)]
            $tariffs = $this->getTariffs($config);
            $serviceTariffs = $this->pullTariffs($config);
            //$roomTypes array[roomId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //$priceCaches array [roomTypeId][tariffId][date => PriceCache]
            $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [],
                true,
                $this->roomManager->useCategories
            );


            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                $currentDatePrices = [];
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                    $currentTypePrices = [];
                    /** @var \DateTime $day */
                    foreach ($tariffs as $tariffId => $tariff) {

                        if (!isset($serviceTariffs[$tariff['syncId']])) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'], $serviceTariffs[$tariff['syncId']]['rooms'])) {
                            continue;
                        }

                        if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {

                            /** @var PriceCache $currentDatePriceCache */
                            $currentDatePriceCache = $priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                            $currentDatePrice = $currentDatePriceCache->getPrice() ?
                                $currentDatePriceCache->getPrice() : null;

                            $currentTypePrices[$tariff['syncId']] =  $currentDatePrice;
                        }
                    }
                    if (count($currentTypePrices) > 0) {
                        $currentDatePrices[$roomTypeInfo['syncId']] = $currentTypePrices;
                    }
                }
                if (count($currentDatePrices) > 0) {
                    $pricesData[] = ['day' => $day->format('Y-m-d'), 'prices' => $currentDatePrices];
                }
            }

            if (!isset($pricesData)) {
                continue;
            }

            $request = $this->getRequestArray($config->getHotelId(), 'set_calendar', $pricesData);
            dump($request);exit();
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param RoomType $roomType
     * @return boolean
     * @throw \Exception
     */
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var HundredOneHotelsConfig $config */
            $roomTypes = $this->getRoomTypes($config);
            $tariffs = $this->getTariffs($config);
            $serviceTariffs = $this->pullTariffs($config);
            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                [],
                true
            );
            $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                [],
                true
            );

            $restrictionsData = [];
            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
                $currentDateRestrictions = ['day' => $day->format('Y-m-d')];
                $closed = $closedToArrival = $closedToDeparture = $minStay = [];
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                    foreach ($tariffs as $tariffId => $tariff) {

                        if (!isset($serviceTariffs[$tariff['syncId']])) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'], $serviceTariffs[$tariff['syncId']]['rooms'])) {
                            continue;
                        }

                        $price = false;
                        if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                            $price = true;
                        }

                        if (isset($restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {

                            /** @var Restriction $maxiBookingRestrictionObject */
                            $maxiBookingRestrictionObject = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];

                            $closed[$roomTypeInfo['syncId']][$tariff['syncId']] = $maxiBookingRestrictionObject->getClosed() || !$price ? 1 : 0;

                            $closedToArrival[$roomTypeInfo['syncId']][$tariff['syncId']] = $maxiBookingRestrictionObject->getClosedOnArrival() ? 1 : 0;

                            $closedToDeparture[$roomTypeInfo['syncId']][$tariff['syncId']] = $maxiBookingRestrictionObject->getClosedOnDeparture() ? 1 : 0;

                            $minStay[$roomTypeInfo['syncId']][$tariff['syncId']] = (int)$maxiBookingRestrictionObject->getMinStay();
                        }
                    }
                }
                if (count($closed) > 0) {
                    $currentDateRestrictions['closed'] = $closed;
                }
                if (count($closedToDeparture) > 0) {
                    $currentDateRestrictions['closed_to_departure'] = $closedToDeparture;
                }
                if (count($closedToArrival) > 0) {
                    $currentDateRestrictions['closed_to_arrival'] = $closedToArrival;
                }
                if (count($minStay) > 0) {
                    $currentDateRestrictions['min_stay'] = $minStay;
                }
                $restrictionsData[] = $currentDateRestrictions;
            }

            if (!isset($restrictionsData)) {
                continue;
            }

            $request = $this->getRequestArray($config->getHotelId(), 'set_calendar', $restrictionsData);
            dump($request);exit();
            $sendResult = $this->send(static::BASE_URL, $request, null, true, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
    }

    /**
     * Create packages from service request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throw \Exception
     */
    public function createPackages()
    {
        return $this->pullOrders();
    }

    /**
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders()
    {
        $result = true;

        foreach ($this->getConfig() as $config) {

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Booking:reservations.xml.twig',
                ['config' => $config, 'lastChange' => false]
            );
            $serviceOrders = $this->sendJson(static::BASE_URL, $request, null, true);
            $this->log('Reservations count: ' . count($serviceOrders['data']));

            foreach ($serviceOrders['data'] as $serviceOrder) {

                if ((string)$serviceOrder['state'] == 'modified') {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        //TODO: Проверить booking_id или id. В документации в респонзе id, в описании booking_id
                        'channelManagerId' => (string)$serviceOrder['booking_id'],
                        'channelManagerType' => self::CHANNEL_MANAGER_TYPE
                    ]
                );
                if ((string)$serviceOrder['state'] == 'modified') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }
                //TODO: Разобраться с остальными статусами
                //new
                if ((string)$serviceOrder['state'] == 1 && !$order) {
                    $result = $this->createOrder($serviceOrder, $config, $order);
                    $this->notify($result, self::CHANNEL_MANAGER_TYPE, 'new');
                }

                //delete
                if ((string)$serviceOrder['state'] == 2 && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, self::CHANNEL_MANAGER_TYPE, 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;

                };

                if (in_array((string)$serviceOrder['state'], ['modified', 'cancelled']) && !$order) {
                    $this->notifyError(
                        'booking',
                        '#' . $serviceOrder['booking_id'] . ' ' .
                        $serviceOrder['contact_last_name'] . ' ' . $serviceOrder['contact_first_name']
                    );
                }
            };
        }

        return $result;
    }

    /**
     * @param $orderData
     * @param ChannelManagerConfigInterface $config
     * @param Order $order
     * @return Order
     */
    private function createOrder(
        $orderData,
        ChannelManagerConfigInterface $config,
        Order $order = null
    )
    {
        $helper = $this->container->get('mbh.helper');

        $services = $this->getServices($config);

        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$orderData['contact_last_name'],
            (string)$orderData['contact_first_name'],
            null,
            null,
            empty((string)$orderData['contact_email']) ? null : (string)$orderData['contact_email'],
            empty((string)$orderData['contact_phone']) ? null : (string)$orderData['contact_phone']
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
            foreach ($order->getFee() as $cashDoc) {
                $this->dm->remove($cashDoc);
                $this->dm->flush();
            }
            $order->setChannelManagerStatus('modified');
            $order->setDeletedAt(null);
        }

        $order->setChannelManagerType(self::CHANNEL_MANAGER_TYPE)
            //TODO: Проверить booking_id или id. В документации в репонзе id, в описании booking_id
            ->setChannelManagerId((string)$orderData['booking_id'])
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderData['sum'])
            ->setOriginalPrice($orderData['sum'])
            ->setTotalOverwrite($orderData['sum']);

        $this->dm->persist($order);
        $this->dm->flush();

        //fee
//        if (!empty((float)$reservation->commissionamount)) {
//            $fee = new CashDocument();
//            $fee->setIsConfirmed(false)
//                ->setIsPaid(false)
//                ->setMethod('electronic')
//                ->setOperation('fee')
//                ->setOrder($order)
//                ->setTouristPayer($payer)
//                ->setTotal($this->currencyConvertToRub($config, (float)$reservation->commissionamount));
//            $this->dm->persist($fee);
//            $this->dm->flush();
//        }
        //Сортируем данные о типах номеров
        $rooms = $orderData['rooms'];
        foreach ($rooms as $key => $row) {
            $date[$key] = $row['day'];
            $roomId[$key] = $row['room_id'];
        }

        $sortedRooms = array_multisort($date, SORT_ASC, $roomId, SORT_ASC, $rooms);

        $currentPlacementId = 0;
        $currentPackage = new Package();

        //packages
        foreach ($sortedRooms as $room) {
            if ($currentPlacementId != $room['placement_id']) {
                $currentPackage = $this->createPackage($room, $config);
            }

            $currentPackage->setEnd(date_add($currentPackage->getEnd(), new \DateInterval('P1D')));

//            foreach ($orderData['guests'] as $guest) {
//                if ($guest['“placement_id”'] == $orderData['placement_id']) {
//                    $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
//                        (string)$guest[''],
//                        (string)$customer->first_name,
//                        null,
//                        null,
//                        empty((string)$customer->email) ? null : (string)$customer->email,
//                        empty((string)$customer->telephone) ? null : (string)$customer->telephone,
//                        empty((string)$customer->address) ? null : (string)$customer->address,
//                        empty($payerNote) ? null : $payerNote
//                    );
//                }
//            }

            //guests
            if ($payer->getFirstName() . ' ' . $payer->getLastName() == (string)$package['contact_name']) {
                $guest = $payer;
            } else {
                $guest = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
                    'н/д',
                    (string)$room->guest_name
                );
            }


            $packageNote = 'remarks: ' . $room->remarks . '; extra_info: ' . $room->extra_info . '; facilities: ' . $room->facilities . '; max_children: ' . $room->max_children;
            $packageNote .= '; commissionamount=' . $room->commissionamount . '; currencycode = ' . $room->currencycode . '; ';
            $packageNote .= $errorMessage;

            $packageTotal = $this->currencyConvertToRub($config, (float)$total);

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
     * @param $room
     * @param HundredOneHotelsConfig $config
     */
    private function createPackage($room, $config)
    {
        $corrupted = false;
        $errorMessage = '';
        $tariffs = $this->getTariffs($config, true);
        $roomTypes = $this->getRoomTypes($config, true);

        $package = new Package();
        $package->setBegin(\DateTime::createFromFormat('Y-m-d', $room['day']));

        //roomType
        if (isset($roomTypes[(string)$room['room_id']])) {
            $roomType = $roomTypes[(string)$room['room_id']]['doc'];
        } else {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $config->getHotelId(),
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $corrupted = true;
            $errorMessage = $this->container->get('translator')
                ->trans('services.hundredOneHotels.invalid_room_type_id', ['%id%' => (string)$room['room_id']]);

            if (!$roomType) {
                return;
            }
        }

        //array [service TariffId][syncId(service TariffId)=> doc(maxi Tariff)]
        if (isset($tariffs[(string)$room['placement_id']])) {
            $tariff = $tariffs[(string)$room['placement_id']]['doc'];
        } else {
            $tariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->findOneBy(
                [
                    'hotel.id' => $config->getHotelId(),
                    'isEnabled' => true,
                    'deletedAt' => null
                ]
            );
            $corrupted = true;
            $errorMessage = $this->container->get('translator')
                ->trans('services.hundredOneHotels.invalid_tariff_id', ['%id%' => (string)$room['placement_id']]);

            if (!$tariff) {
                //TODO: Обработать ситуцацию, когда нет ни одного тарифа
                return;
            }
        }
        $date = \DateTime::createFromFormat('Y-m-d', $room['day']);
        $package->setTariff($tariff);
        $packagePrices[] = new PackagePrice($room , $room['price'], $tariff);

        $package
            ->setChannelManagerId((string)$room['id'])
            ->setChannelManagerType(self::CHANNEL_MANAGER_TYPE)
            ->setBegin($date)
            ->setEnd(date_add($package->getBegin(), new \DateInterval('P1D')))
            ->setRoomType($roomType)
            ->setTariff($tariff)
            ->setAdults((int)$room['occupants'])
            ->setChildren(0)
            ->setPricesByDate($pricesByDate)
            ->setPrices($packagePrices)
            ->setPrice($packageTotal)
            ->setOriginalPrice((float)$total)
            ->setTotalOverwrite($packageTotal)
            ->setNote($packageNote)
            ->setOrder($order)
            ->setCorrupted($corrupted)
            ->addTourist($guest);
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $request = $this->getRequestArray($config->getHotelId(), 'get_hotel');
        $jsonResponse = $this->send(static::BASE_URL, $request, null, true);
        $response = json_decode($jsonResponse, true);

        $rooms = [];
        foreach ($response['data']['rooms'] as $roomType) {
            $rooms[$roomType['id']] = $roomType['name'];
        }

        return $rooms;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        //TODO: Реализовать добавление другой информации по необходимости(к примеру названия тарифа)
        $result = [];

        $request = $this->getRequestArray($config->getHotelId(), 'get_hotel');

        $jsonResponse = $this->send(static::BASE_URL, $request);
        $response = json_decode($jsonResponse, true);

        foreach ($response['data']['rooms'] as $roomType) {
            foreach ($roomType['placements'] as $placement) {
                $result[$placement['id']] = [
                    'title' => $placement['name'] . "\n(". $roomType['name'] .')',
                    'rooms' => [$roomType['id']]
                ];
            }
        }
        
        return $result;
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }
        $response = json_decode($response, true);

        $responseCode = $response['response'];
        return $responseCode == 1 ? true : false;
    }

    /**
     * return data for request with specified service name
     * @param $hotel_id
     * @param $serviceName
     * @param $data
     * @return array
     */
    private function getRequestArray($hotel_id, $serviceName, $data = null)
    {
        $template = [
            'api_key' => self::API_KEY,
            'hotel_id' => $hotel_id,
            'service' => $serviceName
        ];
        if ($data) {
            $template['data'] = $data;
        }
        //отправляемые сообщения должны содержать один POST параметр 'request', содержащий данные в json-формате
        $requestData = ['request' => json_encode($template)];
        return $requestData;
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        //TODO: Реализовать, при необходимости, функционал закрытия продаж
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        $this->log($request->getContent());

        return new Response('OK');
    }
}