<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\OrderInfo;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\HOHRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\PackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\CashBundle\Document\CashDocument;

class HundredOneHotels extends Base
{
    /**
     * Config class
     */
    const CONFIG = 'HundredOneHotelsConfig';

    const CHANNEL_MANAGER_TYPE = 'hundredOneHotels';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://101hotels.info/api2';

    const CHANNEL_MANAGER_TYPE_DISPLAYED = '101Hotels';

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

            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')->setInitData($config);

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
                    $requestFormatter->addSingleParamCondition($day, $requestFormatter::QUOTA, $roomTypeInfo['syncId'], $roomQuotaForCurrentDate);
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
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
                true
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
                        $requestFormatter->addDoubleParamCondition($day, $requestFormatter::PRICES, $roomTypeInfo['syncId'], $tariff['syncId'], $currentDatePrice);
                    }
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest();
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
            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')->setInitData($config);
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
                                $requestFormatter::CLOSED,
                                $roomTypeInfo['syncId'],
                                $maxiBookingRestrictionObject->getClosed() || !$price ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_ARRIVAL,
                                $roomTypeInfo['syncId'],
                                $tariff['syncId'],
                                $maxiBookingRestrictionObject->getClosedOnArrival() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_DEPARTURE,
                                $roomTypeInfo['syncId'],
                                $tariff['syncId'],
                                $maxiBookingRestrictionObject->getClosedOnDeparture() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::MIN_STAY,
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
            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')
                ->setInitData($config, 'get_bookings');
            $startTime = new \DateTime('- 1 day');
            $endTime = new \DateTime();
            $requestFormatter->addDateCondition($startTime, $endTime);
            $request = $requestFormatter->getRequest();

            $serviceOrders = $this->send(static::BASE_URL, $request, null, true, true);
            //$this->log('Reservations: ' . $serviceOrders);
            $serviceOrders = json_decode($serviceOrders, true);

            $this->log('Reservations count: ' . count($serviceOrders['data']));

            $tariffs = $this->getTariffs($config, true);
            $roomTypes = $this->getRoomTypes($config, true);

            foreach ($serviceOrders['data'] as $serviceOrder) {
                /** @var OrderInfo $orderInfo */
                $orderInfo = $this->container->get('mbh.channelmanager.hoh_order_info')
                    ->setInitData($serviceOrder, $config, $tariffs, $roomTypes);

                if ($orderInfo->getLastAction() == 'modified') {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                //old order
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => $orderInfo->getChannelManagerId(),
                        'channelManagerType' => self::CHANNEL_MANAGER_TYPE
                    ]
                );
                if ($orderInfo->getLastAction() == 'modified') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }
                //new
                if ($orderInfo->getLastAction() == 'created' && !$order) {
                    $result = $this->createOrder($orderInfo, $order);
                    $this->notify($result, self::CHANNEL_MANAGER_TYPE, 'new');
                }

                //edited
                if ($orderInfo->getLastAction() == 'modified'
                    && $order && $order->getChannelManagerEditDateTime() != $orderInfo->getModifiedDate()) {
                    $result = $this->createOrder($orderInfo, $order);
                    $this->notify($result, self::CHANNEL_MANAGER_TYPE, 'edit');
                }

                //delete
                if ($orderInfo->getLastAction() == 'canceled' && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, self::CHANNEL_MANAGER_TYPE, 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (in_array($orderInfo->getLastAction(), ['modified', 'cancelled']) && !$order) {
                    $this->notifyError(
                        self::CHANNEL_MANAGER_TYPE,
                        '#' . $orderInfo->getChannelManagerId() . ' ' . $orderInfo->getPayerName()
                    );
                }
            };
        }

        return $result;
    }

    /**
     * @param OrderInfo $orderInfo
     * @param Order $order
     * @return Order
     */
    private function createOrder($orderInfo, Order $order = null)
    {
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
            $order->setChannelManagerEditDateTime($orderInfo->getModifiedDate());
            $order->setDeletedAt(null);
        }

        $order->setChannelManagerType(self::CHANNEL_MANAGER_TYPE_DISPLAYED)
            ->setChannelManagerId($orderInfo->getBookingId())
            ->setMainTourist($orderInfo->getPayer())
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderInfo->getOrderPrice())
            ->setOriginalPrice($orderInfo->getOrderPrice())
            ->setTotalOverwrite($orderInfo->getOrderPrice());

        $this->dm->persist($order);
        $this->dm->flush();

        /**
         * Тип платежа
         * 1 – наличными при заселении
         * 2 – безналичная форма расчета
         * 3 – картой при заселении
         * 4 – картой на сайте при бронировании (предоплата)
         * 5 – картой на сайте после бронирования (постоплата)
         */
        if ($orderInfo->getPayType() == 4 || $orderInfo->getPayType() == 5 || $orderInfo->getPayType() == 2) {
            $cashDoc = new CashDocument();
            $cashDoc->setIsConfirmed(false)
                ->setIsPaid(true)
                ->setMethod('electronic')
                ->setOperation('in')
                ->setOrder($order)
                ->setDocumentDate(new \DateTime())
                ->setTouristPayer($orderInfo->getPayer())
                ->setTotal($orderInfo->getOrderPrice());
            if ($orderInfo->getPayType() == 2) {
                $cashDoc->setIsPaid(false)->setMethod('cashless');
                $order->setNote($this->container->get('translator')->trans('services.hundredOneHotels.cash_document_not_paid'));
            }
            $this->dm->persist($cashDoc);
            $this->dm->flush();
        }

        foreach ($orderInfo->getPackages() as $packageInfo) {
            $package = $this->createPackage($packageInfo, $order);
            $order->addPackage($package);
            $this->dm->persist($package);
            $this->dm->flush();
        }

        $order->setTotalOverwrite($orderInfo->getOrderPrice());
        $this->dm->persist($order);
        $this->dm->flush();

        return $order;
    }

    /**
     * @param PackageInfo $packageInfo
     * @param Order $order
     * @return Package
     */
    private function createPackage($packageInfo, Order $order)
    {
        $package = new Package();
        $package
            ->setChannelManagerType(self::CHANNEL_MANAGER_TYPE_DISPLAYED)
            ->setBegin($packageInfo->getBeginDate())
            ->setEnd($packageInfo->getEndDate())
            ->setRoomType($packageInfo->getRoomType())
            ->setTariff($packageInfo->getTariff())
            ->setAdults($packageInfo->getOccupantsCount())
            ->setChildren(0)
            ->setPrices($packageInfo->getPackagePrices())
            ->setPrice($packageInfo->getTotalPrice())
            ->setOriginalPrice($packageInfo->getTotalPrice())
            ->setTotalOverwrite($packageInfo->getTotalPrice())
            ->setNote($packageInfo->getErrorMessage())
            ->setOrder($order)
            ->setCorrupted($packageInfo->getIsCorrupted());
        foreach ($packageInfo->getTourists() as $touristInfo)
        {
            $package->addTourist($touristInfo);
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
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')->setInitData($config, 'get_hotel');
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
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')->setInitData($config, 'get_hotel');
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
     * @param HundredOneHotelsConfig $config
     * @return null
     */
    public function sendTestRequestAndGetErrorMessage(HundredOneHotelsConfig $config)
    {
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')
            ->setInitData($config, 'get_hotel');
        $request = $requestFormatter->getRequest();
        $response = $this->send(self::BASE_URL, $request, null, true);
        $response = json_decode($response, true);
        if ($response['response'] === 1) {
            return null;
        } else {
            $error = $response['errors'][0];
            $errorCode = $error['code'];
            if($errorCode == 5) {
                return 'form.hundredOneHotels.error.invalid_api_key';
            }
            if ($errorCode == 8) {
                return 'form.hundredOneHotels.error.invalid_hotel_id';
            }
            return $error['message'];
        }
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return bool|void
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $config = $this->getConfig()[0];
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter')->setInitData($config);
        $firstDate = new \DateTime();
        $endDate = new \DateTime('+5 year');
        $roomTypes = $this->getRoomTypes($config);
        $closedData[] = [
            'start' => $requestFormatter->formatDate($firstDate),
            'end' => $requestFormatter->formatDate($endDate)
        ];
        foreach ($roomTypes as $roomType) {
            $closedData[0]['closed'][$roomType['syncId']] = 1;
        }
        $requestFormatter->setRequestedData($closedData);
        $request = $requestFormatter->getRequest();
        $sendResult = $this->send(static::BASE_URL, $request, null, true);
        $response = json_decode($sendResult, true);
        $result = false;
        if ($response) {
            $result = $this->checkResponse($sendResult);
        }

        $this->log($sendResult);
        return $result;
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