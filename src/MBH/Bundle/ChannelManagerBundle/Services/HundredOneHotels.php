<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\Tourist;
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

            $requestFormatter = new HOHRequestFormatter($config);

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

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                    $roomQuotaForCurrentDate = 0;
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    }
                    $requestFormatter->addSingleParamCondition($day, HOHRequestFormatter::QUOTA, $roomTypeInfo['syncId'], $roomQuotaForCurrentDate);
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
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
        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $requestFormatter = new HOHRequestFormatter($config);

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
                foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
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
                        } else {
                            $currentDatePrice = 0;
                        }
                        $requestFormatter->addDoubleParamCondition($day, HOHRequestFormatter::PRICES, $roomTypeInfo['syncId'], $tariff['syncId'], $currentDatePrice);
                    }
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
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
            $requestFormatter = new HOHRequestFormatter($config);
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

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                /** @var \DateTime $day */
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

                            $requestFormatter->addSingleParamCondition($day,
                                HOHRequestFormatter::CLOSED,
                                $roomTypeInfo['syncId'],
                                $maxiBookingRestrictionObject->getClosed() || !$price ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                HOHRequestFormatter::CLOSED_TO_ARRIVAL,
                                $roomTypeInfo['syncId'],
                                $tariff['syncId'],
                                $maxiBookingRestrictionObject->getClosedOnArrival() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                HOHRequestFormatter::CLOSED_TO_DEPARTURE,
                                $roomTypeInfo['syncId'],
                                $tariff['syncId'],
                                $maxiBookingRestrictionObject->getClosedOnDeparture() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                HOHRequestFormatter::MIN_STAY,
                                $roomTypeInfo['syncId'],
                                $tariff['syncId'],
                                (int)$maxiBookingRestrictionObject->getMinStay());
                        }
                    }
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
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

            $requestFormatter = new HOHRequestFormatter($config, 'get_bookings');
            $request = $requestFormatter->getRequest();
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
                        'channelManagerId' => (string)$serviceOrder['id'],
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
                        self::CHANNEL_MANAGER_TYPE,
                        '#' . $serviceOrder['id'] . ' ' .
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
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$orderData['contact_last_name'],
            (string)$orderData['contact_first_name'],
            null,
            null,
            isset($orderData['contact_email']) ? (string)$orderData['contact_email'] : null,
            isset($orderData['contact_phone']) ? (string)$orderData['contact_phone'] : null
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
            ->setChannelManagerId((string)$orderData['id'])
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderData['sum'])
            ->setOriginalPrice($orderData['sum'])
            ->setTotalOverwrite($orderData['sum']);

        $this->dm->persist($order);
        $this->dm->flush();

        //Разбиваем получаемый массив данных о размещениях по типам размещений
        $roomTypeData = [];
        foreach ($orderData['rooms'] as $currentDatePlacement) {
            $roomTypeData[$currentDatePlacement['placement_id']]['roomData'][] = $currentDatePlacement;
        }

        //Добавляем данные о гостях
        foreach ($orderData['guests'] as $guest) {
            $roomTypeData[$guest['placement_id']]['guests'][] = $guest;
        }

        $tariffs = $this->getTariffs($config, true);
        $roomTypes = $this->getRoomTypes($config, true);

        //packages
        foreach ($roomTypeData as $placementType) {
            //TODO: Не могу найти как можно забронировать в 1 брони номера на разные даты, исходя из этого рассчитываю, что такого быть не может
            //Создаем бронь для всех комнат, так как в параметре qty, отображающем кол-во комнат, может быть указано несколько комнат
            for ($i = 0; $i < (int)$placementType['qty']; $i++) {
                $package = $this->createPackage($placementType[$i], $config, $tariffs, $roomTypes, $order);
                $order->addPackage($package);
            }
        }
        $order->setTotalOverwrite((float)$orderData['sum']);
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param $roomTypeData
     * @param ChannelManagerConfigInterface $config
     * @param $tariffs
     * @param $roomTypes
     * @param Order $order
     * @return Package
     * @throws Exception
     */
    private function createPackage($roomTypeData, $config, $tariffs, $roomTypes, Order $order)
    {
        $corrupted = false;
        $errorMessage = '';

        //Данные о размещении одинаковы для всех элементов массива, представляющего данные о размещениях по дням
        $packageCommonData = $roomTypeData['roomData'][0];

        //getting current room type
        if (isset($roomTypes[(string)$packageCommonData['room_id']])) {
            $roomType = $roomTypes[(string)$packageCommonData['room_id']]['doc'];
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
                ->trans('services.hundredOneHotels.invalid_room_type_id', ['%id%' => (string)$packageCommonData['room_id']]);
            if (!$roomType) {
                throw new Exception($this->container->get('translator')->trans('services.hundredOneHotels.nor_one_room_type'));
            }
        }

        //getting current tariff
        if (isset($tariffs[(string)$packageCommonData['placement_id']])) {
            $tariff = $tariffs[(string)$packageCommonData['placement_id']]['doc'];
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
                ->trans('services.hundredOneHotels.invalid_tariff_id', ['%id%' => (string)$packageCommonData['placement_id']]);

            if (!$tariff) {
                throw new Exception($this->container->get('translator')->trans('services.hundredOneHotels.nor_one_tariff'));
            }
        }

        $packagePrices = [];
        $totalPrice = 0;
        foreach ($roomTypeData['roomData'] as $currentDatePlacementData) {
            $currentDate = \DateTime::createFromFormat('Y-m-d', $currentDatePlacementData['day']);
            $price = (int)$currentDatePlacementData['price']; 
            $packagePrices[] = new PackagePrice($currentDate, $price, $tariff);
            $totalPrice += $price;
        }

        $beginDate = \DateTime::createFromFormat('Y-m-d', $packageCommonData['day']);

        $package = new Package();
        $package
            ->setChannelManagerId((string)$packageCommonData['id'])
            ->setChannelManagerType(self::CHANNEL_MANAGER_TYPE)
            ->setBegin($beginDate)
            ->setEnd(date_add($package->getBegin(), new \DateInterval('P1D')))
            ->setRoomType($roomType)
            ->setTariff($tariff)
            ->setAdults((int)$packageCommonData['occupants'])
            ->setChildren(0)
            ->setPrices($packagePrices)
            ->setPrice($totalPrice)
            ->setOriginalPrice($totalPrice)
            ->setTotalOverwrite($totalPrice)
            ->setNote($errorMessage)
            ->setOrder($order)
            ->setCorrupted($corrupted);

        $touristRepository = $this->dm->getRepository('MBHPackageBundle:Tourist');
        foreach ($roomTypeData['guests'] as $guestData) {
            $touristNameData = explode(' ',$guestData['name']);
            /** @var Tourist $tourist */
            $tourist = $touristRepository->fetchOrCreate(
                $touristNameData[0],
                isset($touristNameData[1]) ? $touristNameData[1] : null
            );
            $package->addTourist($tourist);
        }
        return $package;
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $requestFormatter = new HOHRequestFormatter($config, 'get_hotel');
        $request = $requestFormatter->getRequest();
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
        $requestFormatter = new HOHRequestFormatter($config, 'get_hotel');
        $request = $requestFormatter->getRequest();

        $result = [];
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