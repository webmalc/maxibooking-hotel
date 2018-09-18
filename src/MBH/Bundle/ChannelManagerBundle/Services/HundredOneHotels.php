<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\OrderInfo;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\HOHRequestFormatter;
use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\HundredOneHotels\PackageInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\CashBundle\Document\CashDocument;
use Symfony\Component\HttpFoundation\Response;

class HundredOneHotels extends Base
{
    const UNAVAIBLE_PRICES = [
    ];

    const UNAVAIBLE_RESTRICTIONS = [
        'maxStay' => null,
        'minStayArrival' => null,
        'maxStayArrival' => null,
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
    ];

    /**
     * Config class
     */
    const CONFIG = 'HundredOneHotelsConfig';

    const CHANNEL_MANAGER_TYPE = '101Hotels';

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
        /** @var HundredOneHotelsConfig $config */
        foreach ($this->getConfig() as $config) {
            $this->log('begin update rooms for hotel "' . $config->getHotel()->getName() . '"');

            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');

            /** @var HundredOneHotelsConfig $config */
            //$roomTypes array[roomTypeId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config, true);
            //array[roomTypeId][tariffId][date('d.m.Y') => RoomCache]
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                foreach ($roomTypes as $serviceRoomTypeId => $roomTypeInfo) {
                    /** @var RoomType $roomType */
                    $roomType = $roomTypeInfo['doc'];
                    $roomTypeId = $roomType->getId();
                    $roomQuotaForCurrentDate = 0;
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    }
                    $requestFormatter->addSingleParamCondition($day, $requestFormatter::QUOTA, $serviceRoomTypeId,
                        $roomQuotaForCurrentDate);
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest($config);
            $this->log(json_encode($request));

            $sendResult = $this->send(static::BASE_URL, $request, null, true);
            $result = $this->checkResponse($sendResult);

            $this->log('response for update rooms request:');
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
        $calc = $this->container->get('mbh.calculation');
        // iterate hotels
        /** @var HundredOneHotelsConfig $config */
        foreach ($this->getConfig() as $config) {
            $this->log('begin update prices for hotel "' . $config->getHotel()->getName() . '"');

            //array [service TariffId][syncId(service TariffId)=> doc(maxi Tariff)]
            $tariffs = $this->getTariffs($config, true);

            $serviceTariffs = $this->pullTariffs($config);
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
            //$roomTypes array[roomId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config, true);
            //$priceCaches array [roomTypeId][tariffId][date => PriceCache]
            $priceCachesCallback = function () use ($begin, $end, $config, $roomType) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $config->getHotel(),
                    $this->getRoomTypeArray($roomType),
                    [],
                    true
                );
            };
            $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                /** @var \DateTime $day */
                foreach ($roomTypes as $serviceRoomTypeId => $roomTypeInfo) {
                    /** @var RoomType $roomType */
                    $roomType = $roomTypeInfo['doc'];
                    $roomTypeId = $roomType->getId();
                    foreach ($tariffs as $serviceTariffId => $tariffInfo) {
                        /** @var Tariff $tariff */
                        $tariff = $tariffInfo['doc'];
                        $tariffId = $tariff->getId();
                        $tariffChildOptions = $tariff->getChildOptions();
                        //Если тариф дочерний, берем данные о ценах по id родительского тарифа.
                        $syncPricesTariffId = ($tariff->getParent() && $tariffChildOptions->isInheritPrices())
                            ? $tariff->getParent()->getId()
                            : $tariffId;

                        if (!isset($serviceTariffs[$serviceTariffId])) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$serviceTariffId]['rooms']) && !in_array($serviceRoomTypeId,
                                $serviceTariffs[$serviceTariffId]['rooms'])
                        ) {
                            continue;
                        }

                        if (isset($priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')])) {
                            /** @var PriceCache $currentDatePriceCache */
                            $occupantCount = $serviceTariffs[$serviceTariffId]['occupantCount'];
                            $currentDatePriceCache = $priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')];
                            $priceFinal = $calc->calcPrices($currentDatePriceCache->getRoomType(), $tariff, $day, $day,
                                $occupantCount);
                            $currentDatePrice = isset($priceFinal[$occupantCount . '_0']) ? $priceFinal[$occupantCount . '_0']['total'] : 0;
                        } else {
                            $currentDatePrice = 0;
                        }
                        $requestFormatter->addDoubleParamCondition($day, $requestFormatter::PRICES, $serviceRoomTypeId,
                            $serviceTariffId, $currentDatePrice);
                    }
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest($config);
            $this->log(json_encode($request));
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log('response for update prices request:');
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
        /** @var HundredOneHotelsConfig $config */
        foreach ($this->getConfig() as $config) {
            $this->log('begin update restrictions for hotel "' . $config->getHotel()->getName() . '"');
            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
            $roomTypes = $this->getRoomTypes($config, true);
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

            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                /** @var \DateTime $day */
                foreach ($roomTypes as $serviceRoomTypeId => $roomTypeInfo) {
                    /** @var RoomType $roomType */
                    $roomType = $roomTypeInfo['doc'];
                    $roomTypeId = $roomType->getId();
                    foreach ($tariffs as $serviceTariffId => $tariffInfo) {
                        /** @var Tariff $tariff */
                        $tariff = $tariffInfo['doc'];
                        $tariffId = $tariff->getId();
                        $tariffChildOptions = $tariff->getChildOptions();
                        //Если тариф дочерний, берем данные о ценах по id родительского тарифа.
                        $syncPricesTariffId = ($tariff->getParent() && $tariffChildOptions->isInheritPrices())
                            ? $tariff->getParent()->getId()
                            : $tariffId;
                        $syncRestrictionsTariffId = ($tariff->getParent() && $tariffChildOptions->isInheritRestrictions())
                            ? $tariff->getParent()->getId()
                            : $tariffId;

                        if (!isset($serviceTariffs[$serviceTariffId])) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$serviceTariffId]['rooms']) && !in_array($serviceRoomTypeId,
                                $serviceTariffs[$serviceTariffId]['rooms'])
                        ) {
                            continue;
                        }

                        $price = false;
                        if (isset($priceCaches[$roomTypeId][$syncPricesTariffId][$day->format('d.m.Y')])) {
                            $price = true;
                        }

                        if (isset($restrictions[$roomTypeId][$syncRestrictionsTariffId][$day->format('d.m.Y')])) {

                            /** @var Restriction $maxiBookingRestrictionObject */
                            $maxiBookingRestrictionObject = $restrictions[$roomTypeId][$syncRestrictionsTariffId][$day->format('d.m.Y')];

                            $requestFormatter->addSingleParamCondition($day,
                                $requestFormatter::CLOSED,
                                $serviceRoomTypeId,
                                $maxiBookingRestrictionObject->getClosed() || !$price ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_ARRIVAL,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                $maxiBookingRestrictionObject->getClosedOnArrival() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_DEPARTURE,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                $maxiBookingRestrictionObject->getClosedOnDeparture() ? 1 : 0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::MIN_STAY,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                (int)$maxiBookingRestrictionObject->getMinStay());
                        } else {
                            $requestFormatter->addSingleParamCondition($day,
                                $requestFormatter::CLOSED,
                                $serviceRoomTypeId,
                                0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_ARRIVAL,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::CLOSED_TO_DEPARTURE,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                0);

                            $requestFormatter->addDoubleParamCondition($day,
                                $requestFormatter::MIN_STAY,
                                $serviceRoomTypeId,
                                $serviceTariffId,
                                1);
                        }
                    }
                }
            }

            if ($requestFormatter->isDataEmpty()) {
                continue;
            }

            $request = $requestFormatter->getRequest($config);
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log('response for update restrictions request:');
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
     * @param string $pullOldStatus
     * @return mixed
     */
    public function pullOrders($pullOldStatus = ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS)
    {
        $result = true;
        $isPulledAllPackages = $pullOldStatus === ChannelManager::OLD_PACKAGES_PULLING_ALL_STATUS;

        /** @var HundredOneHotelsConfig $config */
        foreach ($this->getConfig($isPulledAllPackages) as $config) {
            $this->log('begin pulling orders for hotel "' . $config->getHotel()->getName() . '" with id "' . $config->getHotel()->getId() . '"');
            /** @var HOHRequestFormatter $requestFormatter */
            $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');

            switch ($pullOldStatus) {
                case ChannelManager::OLD_PACKAGES_PULLING_ALL_STATUS:
                    $startTime = new \DateTime('- 1 year');
                    break;
                case ChannelManager::OLD_PACKAGES_PULLING_NOT_STATUS:
                    $startTime = new \DateTime('- 1 hour');
                    break;
                default:
                    $startTime = new \DateTime('- 1 hour');
                    $this->log('Passed invalid $pullOldStatus argument="' . $pullOldStatus . '"');
            }

            $endTime = new \DateTime();
            $requestFormatter->addDateCondition($startTime, $endTime);
            $request = $requestFormatter->getRequest($config, 'get_bookings');

            $serviceOrders = $this->send(static::BASE_URL, $request, null, true);
            $this->log($serviceOrders);
            $serviceOrders = json_decode($serviceOrders, true);

            $tariffs = $this->getTariffs($config, true);
            $roomTypes = $this->getRoomTypes($config, true);

            foreach ($serviceOrders['data'] as $serviceOrder) {
                /** @var OrderInfo $orderInfo */
                $orderInfo = $this->container
                    ->get('mbh.channelmanager.hoh_order_info')
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
                        'channelManagerType' => self::CHANNEL_MANAGER_TYPE,
                    ]
                );
                if ($orderInfo->getLastAction() == 'modified') {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //new
                /**
                 * Status of the booking:
                 * 1 – new
                 * 2 – cancelled
                 * 3 – accepted
                 * 4 – client arrived
                 * 5 – archived
                 * 7 – no-show reported by hotel
                 */
                if (($orderInfo->getLastAction() == 'created' && !$order)
                    || ($orderInfo->getLastAction() == 'modified'
                        && ($orderInfo->getOrderState() == 1 || $orderInfo->getOrderState() == 3) && !$order)
                ) {
                    $result = $this->createOrder($orderInfo, null);
                    $order = $result;
                    $this->notify($result, self::CHANNEL_MANAGER_TYPE, 'new');
                } elseif ($orderInfo->getLastAction() == 'modified'
                    && $order && $order->getChannelManagerEditDateTime() != $orderInfo->getModifiedDate()
                ) {
                    $result = $this->createOrder($orderInfo, $order);
                    $this->notify($result, self::CHANNEL_MANAGER_TYPE, 'edit');
                } elseif ($orderInfo->getLastAction() == 'canceled' && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, self::CHANNEL_MANAGER_TYPE, 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $result = true;
                };

                if (!$order) {
                    if ($orderInfo->getLastAction() === 'modified') {
                        $result = $this->createOrder($orderInfo, null);
                        $this->notifyError(self::CHANNEL_MANAGER_TYPE, $this->getUnexpectedOrderError($result, true));
                    }
                    if ($orderInfo->getLastAction() === 'cancelled') {
                        $this->notifyError(self::CHANNEL_MANAGER_TYPE, $this->getUnexpectedOrderError($result, false));
                    }
                }
            }

            if ($result && $isPulledAllPackages) {
                $config->setIsAllPackagesPulled(true);
                $this->dm->flush();
            }
        }

        if ($isPulledAllPackages) {
            $cm = $this->container->get('mbh.channelmanager');
            $cm->clearAllConfigsInBackground();
            $cm->updateInBackground();;
        }

        return $result;
    }

    /**
     * @param OrderInfo $orderInfo
     * @param Order $order
     * @return Order
     */
    public function createOrder($orderInfo, Order $order = null)
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

        $order->setChannelManagerType(self::CHANNEL_MANAGER_TYPE)
            ->setChannelManagerId($orderInfo->getBookingId())
            ->setMainTourist($orderInfo->getPayer())
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderInfo->getOrderPrice())
            ->setOriginalPrice($orderInfo->getOrderPrice())
            ->setTotalOverwrite($orderInfo->getOrderPrice());

        if ($orderInfo->getUserComment()) {
            $order->setNote($orderInfo->getUserComment());
        }

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
        $payType = '';
        if ($orderInfo->getPayType() == 4 || $orderInfo->getPayType() == 5 || $orderInfo->getPayType() == 2) {
            $payType = 'electronic';
        } elseif ($orderInfo->getPayType() == 2) {
            $payType = 'cashless';
        }

        $savedCashDocument = [];
        foreach ($order->getCashDocuments() as $cashDocument) {
            /** @var CashDocument $cashDocument */
            if ($cashDocument->getTotal() == $orderInfo->getOrderPrice()
                && $cashDocument->getMethod() == $payType
                && $cashDocument->getOperation() == 'in'
            ) {
                $savedCashDocument[] = $cashDocument;
            }
        }

        if (count($savedCashDocument) == 0) {
            if ($payType == 'electronic') {
                $cashDoc = new CashDocument();
                $cashDoc->setIsConfirmed(false)
                    ->setIsPaid(true)
                    ->setMethod('electronic')
                    ->setOperation('in')
                    ->setOrder($order)
                    ->setDocumentDate(new \DateTime())
                    ->setTouristPayer($orderInfo->getPayer())
                    ->setTotal($orderInfo->getOrderPrice());
                if ($payType == 'cashless') {
                    $cashDoc->setIsPaid(false)->setMethod('cashless');
                    $order->setNote($order->getNote() . "\n" . $this->container->get('translator')
                            ->trans('services.hundredOneHotels.cash_document_not_paid'));
                }
                $this->dm->persist($cashDoc);
                $this->dm->flush();
            }
        }

        foreach ($orderInfo->getPackages() as $packageInfo) {
            $package = $this->createPackage($packageInfo, $order);
            $order->addPackage($package);
            //Double persist because of doctrine bug
            $this->dm->persist($package);
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
        foreach ($packageInfo->getTourists() as $touristInfo) {
            $package->addTourist($touristInfo);
        }

        return $package;
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     * @throws \Throwable
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
        $request = $requestFormatter->getRequest($config, 'get_hotel');
        $jsonResponse = $this->send(static::BASE_URL, $request, null, true);
        $response = json_decode($jsonResponse, true);

        $rooms = [];
        if ($this->checkResponse($jsonResponse)) {
            foreach ($response['data']['rooms'] as $roomType) {
                $rooms[$roomType['id']] = $roomType['name'];
            }
        } else {
            $this->log(json_encode($response));
            $this->notifyErrorRequest(
                '101hotels.ru',
                'channelManager.commonCM.notification.request_error.pull_rooms'
            );
        }


        return $rooms;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     * @throws \Throwable
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
        $request = $requestFormatter->getRequest($config, 'get_hotel');

        $result = [];
        $jsonResponse = $this->send(static::BASE_URL, $request);
        $response = json_decode($jsonResponse, true);

        if ($this->checkResponse($jsonResponse)) {
            foreach ($response['data']['rooms'] as $roomType) {
                foreach ($roomType['placements'] as $placement) {
                    $result[$placement['id']] = [
                        'title' => $placement['name']."\n(".$roomType['name'].')',
                        'occupantCount' => $placement['occupancy'],
                        'rooms' => [$roomType['id']],
                    ];
                }
            }
        } else {
            $this->log(json_encode($response));
            $this->notifyErrorRequest(
                '101hotels.ru',
                'channelManager.commonCM.notification.request_error.pull_tariffs'
            );
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
        $isSuccess = $responseCode == 1;
        if (!$isSuccess) {
            $this->addError(json_encode($response['errors']));
        }

        return $isSuccess;
    }

    /**
     * @param HundredOneHotelsConfig $config
     * @return null
     */
    public function sendTestRequestAndGetErrorMessage(HundredOneHotelsConfig $config)
    {
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
        $request = $requestFormatter->getRequest($config, 'get_hotel');
        $response = $this->send(self::BASE_URL, $request, null, true);
        $response = json_decode($response, true);
        if ($this->checkResponse($response)) {
            return null;
        } else {
            $error = $response['errors'][0];
            $errorCode = $error['code'];
            if ($errorCode == 5) {
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
     * @return bool
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $config = $this->getConfig()[0];
        /** @var HOHRequestFormatter $requestFormatter */
        $requestFormatter = $this->container->get('mbh.channelmanager.hoh_request_formatter');
        $firstDate = new \DateTime();
        $endDate = new \DateTime('+5 year');
        $roomTypes = $this->getRoomTypes($config);
        $closedData[] = [
            'start' => $requestFormatter->formatDate($firstDate),
            'end' => $requestFormatter->formatDate($endDate),
        ];
        foreach ($roomTypes as $roomType) {
            $closedData[0]['closed'][$roomType['syncId']] = 1;
        }
        $requestFormatter->setRequestedData($closedData);
        $request = $requestFormatter->getRequest($config);
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