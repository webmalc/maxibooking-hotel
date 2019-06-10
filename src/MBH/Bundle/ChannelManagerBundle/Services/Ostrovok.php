<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiService;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiServiceException;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokDataGenerator;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\ChannelManagerBundle\Document\Service;

/**
 *  ChannelManager service
 */
class Ostrovok extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'OstrovokConfig';

    /**
     * Debug mode on/off
     */
    const TEST = true;

    /**
     * Test url
     */
    const TEST_URL = 'https://extratest.ostrovok.ru';

    /**
     * Test url
     */
    const URL = 'https://ostrovok.ru';

    const CHANNEL_MANAGER_TYPE = 'Ostrovok';

    const SERVICES = [
        1 => 'Buffet breakfast',
        2 => 'Continental breakfast',
        4 => 'American breakfast',
        5 => 'Half board',
        6 => 'Full board',
        7 => 'Breakfast',
        8 => 'Breakfast and Lunch',
        9 => 'Dinner',
        10 => 'Full pansion',

    ];

    /**
     * @var array
     */
    private $params;
    /** @var OstrovokApiService */
    private $apiBrowser;

    private $calculation;
    /**
     * @var string
     */
    private $url = self::URL;
    /** @var OstrovokDataGenerator */
    private $dataGenerator;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->params = $container->getParameter('mbh.channelmanager.services')['ostrovok'];
        !self::TEST ?: $this->url = self::TEST_URL;
        $this->apiBrowser = $container->get('ostrovok_api_service');
        $this->dataGenerator = $container->get('mbh_bundle_channel_manager.lib_ostrovok.ostrovok_data_generator');
        $this->calculation = $container->get('mbh.calculation');
    }

    /**
     * {@inheritDoc}
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        //** Alarma! Островок -  */
        $result = true;
        $rna_request_data = [];
        //Закрыли на год вперед комнаты
        $rooms = $this->pullRooms($config);
        $startDate = new \DateTime("now");
        $endDate = (clone $startDate)->modify("+1 year");
        $hotelId = $config->getHotelId();
        foreach ($rooms as $roomId => $roomName) {
            $rna_request_data['room_categories'][] = $this->dataGenerator->getRnaRoomCategoriesData($roomId, 0, $startDate, $endDate, $hotelId);
        }
        $result = $result && $this->sendApiRequest($rna_request_data, __METHOD__);
        $rna_request_data = [];

        $rate_plans = [];
        try {
            //Цены
            $rate_plans = $this->apiBrowser->getRatePlans(['hotel' => $hotelId]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log($e->getResponse()->getBody()->getContents(), 'error');
            $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
        }

        $data = [];
        foreach ($rate_plans as $rate_plan) {
            if ($rate_plan['parent']) {
                continue;
            }
            if (\count($rate_plan['possible_occupancies'])) {
                foreach ($rate_plan['possible_occupancies'] as $occupancyId) {
                    $data[] = $this->dataGenerator->getRnaOccupanciesData(
                        $occupancyId,
                        $rate_plan['room_category'],
                        $rate_plan['id'],
                        0,
                        $startDate,
                        $endDate,
                        $hotelId);
                }
            }
        }
        $chunkSize = 5;
        if (\count($data) > $chunkSize) {
            $chunks = array_chunk($data, $chunkSize);
            foreach ($chunks as $chunk) {
                $rna_request_data['occupancies'] = $chunk;
                $result = $result && $this->sendApiRequest($rna_request_data, __METHOD__);
            }
        } else {
            $rna_request_data['occupancies'] = $data;
            $result = $result && $this->sendApiRequest($rna_request_data, __METHOD__);
        }

        $rna_request_data = [];

        //Ограничения
        foreach ($rate_plans as $rate_plan) {
            $rna_request_data['rate_plans'][] = $this->dataGenerator->getRnaRestrictionData(
                $rate_plan['room_category'],
                $rate_plan['id'],
                $config->getHotelId(),
                $startDate,
                $endDate,
                1
            );
        }

        $result = $result && $this->sendApiRequest($rna_request_data, __METHOD__);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        $request_data = [];
        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var ChannelManagerConfigInterface $config */
            $roomTypes = $this->getRoomTypes($config);
            $roomTypeIds = $roomType ? [$roomType->getId()] : array_keys($roomTypes);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomTypeIds,
                null,
                true
            );
            $hotelId = $config->getHotelId();
            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                $CMRoomCategory = $roomTypeInfo['syncId'];
                $roomCategoryRNA = [];
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $roomCache */
                        $roomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $leftRooms = $roomCache->getLeftRooms() ?: 0;
                    } else {
                        $leftRooms = 0;
                    }
                    $roomCategoryRNA[] = $this->dataGenerator
                        ->getRnaRoomCategoriesData(
                            $CMRoomCategory,
                            $leftRooms,
                            $day,
                            $day,
                            $hotelId);
                }
                $request_data[] = $this->splitByPeriod($roomCategoryRNA, 'count');
            }
        }

        $rna_request_data['room_categories'] = array_merge(...$request_data);

        return $this->sendApiRequest($rna_request_data, __METHOD__) && $result;
    }

    private function splitByPeriod(array $data, string $valueField)
    {
        $periods = [];
        $currentPeriod = null;
        foreach ($data as $dayData) {
            if ($dayData['plan_date_start_at'] !== $dayData['plan_date_end_at']) {
                throw new OstrovokApiServiceException('Period  have wrong different in dates');
            }
            if (null === $currentPeriod) {
                $currentPeriod = $dayData;
            }

            $endCurrentPeriod = $currentPeriod['plan_date_end_at'];
            $startNewPeriod = $dayData['plan_date_start_at'];

            $endCurrent = new \DateTime("$endCurrentPeriod midnight");
            $startNew = new \DateTime("$startNewPeriod midnight");

            $diff = (int)$endCurrent->diff($startNew)->format('a');
            $moreThanDay = $diff > 1;

            $isDiffValue = $currentPeriod[$valueField] !== $dayData[$valueField];

            if ($isDiffValue || $moreThanDay) {
                $periods[] = $currentPeriod;
                $currentPeriod = $dayData;
            } else {
                $currentPeriod['plan_date_end_at'] = $dayData['plan_date_end_at'];
            }

        }

        $periods[] = $currentPeriod;

        return $periods;
    }


    /**
     * {@inheritDoc}
     */
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);
        try {
            // iterate hotels
            $data = [];
            /** @var ChannelManagerConfigInterface $config */
            foreach ($this->getConfig() as $config) {
                try {
                    $allOccupancies = $this->apiBrowser->getOccupancies(['hotel' => $config->getHotelId()], true);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $this->log($e->getResponse()->getBody()->getContents(), 'error');
                    $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
                }

                $rooms = $config->getRooms()->toArray();
                if (null !== $roomType) {
                    $rooms = array_filter($rooms, function ($room) use ($roomType) {
                        /** @var Room $room */
                        return $room->getRoomType()->getId() === $roomType->getId();
                    });
                }

                $tariffs = $this->getTariffs($config, true);
                $ratePlans = $this->getRatePlansArray($config->getHotelId());

                foreach ($rooms as $room) {
                    /** @var Room $room */
                    $roomType = $room->getRoomType();
                    $CMRoomId = $room->getRoomId();
                    $filteredRatePlans = array_filter($ratePlans, function ($ratePlan) use ($CMRoomId) {
                        return $ratePlan['room_category'] === (int)$CMRoomId;
                    });
                    foreach ($filteredRatePlans as $ratePlanId => $ratePlan) {
                        $tariff = $tariffs[$ratePlanId]['doc'] ?? null;
                        $occupancies = array_intersect_key($allOccupancies, array_flip($ratePlan['possible_occupancies']));
                        foreach ($occupancies as $occupancy) {
                            foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                                if (null === $tariff) {
                                    throw new OstrovokApiServiceException('Tariff is null!!!');
                                }
                                $prices = $this->calculation->calcPrices($roomType, $tariff, $day, $day);
                                $capacity = $occupancy['capacity'] . '_0';
                                $price = $prices[$capacity]['total'] ?? 0;
                                $occupancyData[] = $this->dataGenerator->getRnaOccupanciesData(
                                    $occupancy['id'],
                                    $CMRoomId,
                                    $ratePlanId,
                                    $price,
                                    $day,
                                    $day,
                                    $config->getHotelId()
                                );

                            }
                            $data[] = $this->splitByPeriod($occupancyData, 'price');
                            unset($occupancyData);
                        }
                    }
                }
            }


            $data = array_merge(...$data);
            $chunkSize = 40;
            if (\count($data) < $chunkSize) {
                $result = $this->sendApiRequest(['occupancies' => $data], __METHOD__);
            } else {
                foreach (array_chunk($data, $chunkSize) as $chunk) {
                    $result = $result && $this->sendApiRequest(['occupancies' => $chunk], __METHOD__);
                }
            }
        } catch (OstrovokApiServiceException $e) {
            $result = false;
            $this->log($e->getMessage(), 'CRITICAL');
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
        $rna_request_data = [];
        foreach ($this->getConfig() as $config) {
            //First update Tariff restriction
            $tariffs = $this->getTariffs($config, true);
            $ratePlans = $this->getRatePlansArray($config->getHotelId());
            foreach ($tariffs as $ostrovokTariffId => $tariffDoc) {
                $tariff = $tariffDoc['doc'];
                $ratePlan = $ratePlans[$ostrovokTariffId];
                $data = $this->dataGenerator->getRequestDataRatePlan($tariff, $ratePlan, $config);
                try {
                    $this->apiBrowser->updateRatePlan(
                        $ostrovokTariffId,
                        $config->getHotelId(),
                        $ratePlan['room_category'],
                        $data
                    );
                } catch (OstrovokApiServiceException $exception) {
                    $this->log('Не удалось обновить ограничения для тарифов' . $exception->getMessage());
                    $result = false;
                }
            }


            $configRoomTypes = $this->getRoomTypes($config);
            $configTariffs = $this->getTariffs($config);
            $roomTypeRestrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : array_keys($configRoomTypes),
                array_keys($configTariffs),
                true
            );

            foreach ($tariffs as $ostrovokTariffId => $tariffDoc) {
                foreach ($roomTypeRestrictions as $roomTypeId => $tariffId) {
                    continue;
                }
            }

            foreach ($configRoomTypes as $roomTypeId => $roomDoc) {
                $ostrovokRoomTypeId = $roomDoc['syncId'];
                foreach ($tariffs as $ostrovokTariffId => $tariffDoc) {
                    if ($ratePlans[$ostrovokTariffId]['room_category'] != $ostrovokRoomTypeId) {
                        continue;
                    }
                    $tariffId = $tariffDoc['doc']->getId();
//                    $accordingOstrovokTariffId = $this->getAccordingTariff($ostrovokRoomTypeId, $tariffId, $config, true);
                    if (isset($roomTypeRestrictions[$roomTypeId][$tariffId])) {
                        $arrRestrictons = $roomTypeRestrictions[$roomTypeId][$tariffId];
                        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), (clone $end)->modify('+1 day')) as $day) {
                            if (in_array($day->format('d.m.Y'), array_keys($arrRestrictons))) {
                                /** @var Restriction $restriction */
                                $restriction = $arrRestrictons[$day->format('d.m.Y')];
                                $rna_request_data = array_merge_recursive(
                                    $rna_request_data,
                                    $this->dataGenerator->getRequestDataRnaRestrictions(
                                        $ostrovokRoomTypeId,
                                        $ostrovokTariffId,
                                        $config->getHotelId(),
                                        $day,
                                        $day,
                                        (int)$restriction->getMinStayArrival() ?: null,
                                        (int)$restriction->getMaxStayArrival() ?: null,
                                        (int)$restriction->getMinStay() ?: null,
                                        (int)$restriction->getMaxStay() ?: null,
                                        (bool)$restriction->getClosedOnArrival(),
                                        (bool)$restriction->getClosedOnDeparture(),
                                        (bool)$restriction->getClosed()
                                    )
                                );
                            } else {
                                $rna_request_data = array_merge_recursive(
                                    $rna_request_data,
                                    $this->dataGenerator->getRequestDataRnaRestrictions(
                                        $ostrovokRoomTypeId,
                                        $ostrovokTariffId,
                                        $config->getHotelId(),
                                        $day,
                                        $day
                                    )
                                );
                            }
                        }
                    } else {
                        $rna_request_data = array_merge_recursive(
                            $rna_request_data,
                            $this->dataGenerator->getRequestDataRnaRestrictions(
                                $ostrovokRoomTypeId,
                                $ostrovokTariffId,
                                $config->getHotelId(),
                                $begin,
                                $end
                            )
                        );
                    }
                }
            }
        }

        return $this->sendApiRequest($rna_request_data, __METHOD__) && $result;
    }

    /**
     * {@inheritDoc}
     */
    public function checkResponse($response, array $params = null)
    {
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
     */
    public function pullOrders()
    {
        $result = true;
        $date = (new \DateTime('yesterday midnight'))->format('Y-m-d');
        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            try {
                $bookings = $this->apiBrowser->getBookings([
                    'hotel' => $config->getHotelId(),
                    'modified_at_start_at' => $date
                ]);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $this->log($e->getResponse()->getBody()->getContents(), 'error');
                $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
            }

            $this->log('There are ' . count($bookings) . ' total ' . $date);
            if (!$bookings) {
                continue;
            }

            foreach ($bookings as $reservation) {
                /*$reservationCreatedAt = new \DateTime($reservation['created_at']);
                $now = new \DateTime('midnight');
                if ($reservationCreatedAt < $now) {
                    continue;
                }*/

                $isModified = $reservation['created_at'] !== $reservation['modified_at'];
                /** @var Order $order */

                if ($isModified) {
                    if ($this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->disable('softdeleteable');
                    }
                }
                $order = $this->dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                    [
                        'channelManagerId' => (string)$reservation['uuid'],
                        'channelManagerType' => 'ostrovok',
                    ]
                );
                if ($isModified) {
                    if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $this->dm->getFilterCollection()->enable('softdeleteable');
                    }
                }

                //If new
                if ((string)$reservation['status'] === 'normal' && !$order) {
                    $result = $this->createPackage($reservation, $config);
                    $this->notify($result, 'ostrovok', 'new');
                    $this->log('Order ' . $result->getId() . 'was created.');
                }

                //If modified
                if ((string)$reservation['status'] === 'normal' && $order && $isModified) {
                    if (new \DateTime($order->getChannelManagerEditDateTime()) != new \DateTime(
                            $reservation['modified_at']
                        )
                    ) {
                        $order->setChannelManagerEditDateTime($reservation['modified_at']);
                        $result = $this->createPackage($reservation, $config, $order);
                        $this->notify($result, 'ostrovok', 'edit');
                        $this->log('Order ' . $order->getId() . 'was changed.');
                    }
                }
                //If Cancelled
                if ((string)$reservation['status'] === 'cancelled' && $order) {
                    if ($order->getChannelManagerStatus() !== 'cancelled') {
                        $order->setChannelManagerStatus('cancelled');
                        $order->setChannelManagerEditDateTime($reservation['modified_at']);
                        $this->dm->persist($order);
                        $this->dm->flush();
                        $this->notify($order, 'ostrovok', 'delete');
                        $this->dm->remove($order);
                        $this->dm->flush();
                        $this->log('Order ' . $order->getId() . 'was cancelled.');
                    } else {
                        $this->logger->addInfo('The order already deleted in Ostrovok ' . $order->getChannelManagerId());
                    }


                    $result = true;
                }

                if (($reservation['status'] === 'cancelled' || $isModified) && !$order) {
                    /*$this->notifyError(
                        'ostrovok',
                        '#'.$reservation['uuid'].' '.
                        $reservation['last_name'].' '.$reservation['first_name']
                    );*/
                    $this->log('Error! Бронь существует, но нет в базе. ' . $reservation['uuid']);
                }
            }
        }

        return $result;
    }


    public function createPackage(array $reservation, ChannelManagerConfigInterface $config, Order $order = null)
    {
        $roomTypes = $this->getRoomTypes($config, true);
        $tariffs = $this->getTariffs($config, true);

        //Tourist
        $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->fetchOrCreate(
            (string)$reservation['last_name'],
            (string)$reservation['first_name'],
            null,
            null,
            null,
            null,
            $reservation['email'] ?? null
        );

        //Order
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

        $orderPrice = (float)$reservation['total_amount'];

        $order->setChannelManagerType('ostrovok')
            ->setChannelManagerId($reservation['uuid'])
            ->setMainTourist($payer)
            ->setConfirmed(false)
            ->setStatus('channel_manager')
            ->setPrice($orderPrice)
            ->setOriginalPrice($orderPrice)
            ->setTotalOverwrite($orderPrice)
            ->setNote($reservation['comment']);

        $this->dm->persist($order);
        $this->dm->flush();

        //Package
        $corrupted = false;
        $errorMessage = '';
        if (isset($roomTypes[$reservation['room_category']])) {
            $roomType = $roomTypes[$reservation['room_category']]['doc'];
        } else {
            $roomType = $this->dm->getRepository('MBHHotelBundle:RoomType')->findOneBy(
                [
                    'hotel.id' => $config->getHotel()->getId(),
                    'isEnabled' => true,
                    'deletedAt' => null,
                ]
            );
            $corrupted = true;
            $errorMessage = 'ERROR: invalid roomType #' . (string)$reservation['room_category'];
        }

        $guest = $payer;

        $tariff = $rateId = null;
        $packagePrices = [];
        $priceByDate = [];
        $dayTariffs = [];
        $total = 0;
        foreach ($reservation['price_per_day'] as $dayIndex => $price) {
            $rateId = $reservation['rate_per_day'][$dayIndex];
            if (isset($tariffs[$rateId])) {
                $tariff = $tariffs[$rateId]['doc'];
            }
            if (!$tariff) {
                $tariff = $this->createTariff($config, $rateId);
                $corrupted = true;
                $errorMessage .= 'ERROR: Not mapped rate <' . $tariff->getName();

                if (!$tariff) {
                    continue;
                }
            }

            $total += (float)$price;
            $date = (new \DateTime($reservation['arrive_at']))->modify('+ ' . $dayIndex . ' days');
            $priceByDate[$date->format('d_m_Y')] = $price;
            $packagePrices[] = new PackagePrice($date, (float)$price, $tariff);
            $dayTariffs[] = [
                'tarifId' => $rateId,
                'tariff' => $tariff,
            ];
            $tariff = null;
        }

        if ($total != $reservation['total_amount']) {
            $corrupted = true;
            $errorMessage .= 'ERROR: prices by day not equal total price';
        }

        if (isset($tariffs[$reservation['rate_plan']])) {
            $mainTariff = $tariffs[$reservation['rate_plan']]['doc'];
        } else {
            $mainTariff = $dayTariffs[0]['tariff'];
        }


        $packageNote = $errorMessage;

        $package = new Package();
        $package
            ->setChannelManagerId((string)$reservation['uuid'])
            ->setChannelManagerType('ostrovok')
            ->setBegin(new \DateTime($reservation['arrive_at']))
            ->setEnd(new \DateTime($reservation['depart_at']))
            ->setRoomType($roomType)
            ->setTariff($mainTariff)
            ->setAdults((int)$reservation['adults'])
            ->setChildren((int)count($reservation['children']))
            ->setPrices($packagePrices)
            ->setPrice($reservation['total_amount'])
            ->setTotalOverwrite((float)$reservation['total_amount'])
            ->setNote($packageNote)
            ->setOrder($order)
            ->setCorrupted($corrupted)
            ->addTourist($guest);

        //Services
        $ratePlans = $this->getRatePlansArray($config->getHotelId());
        $services = $this->getServices($config);

        $order->addPackage($package);
        $this->dm->persist($package);
        $this->dm->persist($order);
        $this->dm->flush();

        if (isset($ratePlans[$reservation['rate_plan']])) {
            $ratePlan = $ratePlans[$reservation['rate_plan']];
            if ($ratePlan['meal_plan_available']) {
                $isMealPlanIncluded = $ratePlan['meal_plan_included'];
                $mealPlanCost = $ratePlan['meal_plan_cost'];
                $mealPlanId = $ratePlan['meal_plan'];

                $service = $services[$mealPlanId]['doc'];
                $packageService = new PackageService();
                $packageService->setService($service);
                if (!$isMealPlanIncluded) {
                    /** @var \MBH\Bundle\PriceBundle\Document\Service $service */
                    $packageService
                        ->setPrice($mealPlanCost)
                        ->setIsCustomPrice(true);
                }
                $packageService->setNights((int)count($reservation['rate_per_day']))
                    ->setPersons((int)$reservation['adults'] + (int)count($reservation['children']))
                    ->setAmount(1)
                    ->setPackage($package)
                    ->setNote('ostrovok.autoadd.service.notice');

                $package->addService($packageService);

                $this->dm->persist($packageService);
                $this->dm->flush();
            }
        }

        return $order;
    }

    /**
     * {@inheritDoc}
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        $rate_plans = [];
        try {
            $rate_plans = $this->apiBrowser->getRatePlans(['hotel' => $config->getHotelId()]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log($e->getResponse()->getBody()->getContents(), 'error');
            $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
        }

        $rooms = $this->pullRooms($config);

        $rates = [];
        foreach ($rate_plans as $rate) {
            $rates[$rate['id']] = [
                'title' => $rate['name'],
                'readonly' => false,
                'is_child_rate' => empty($rate['parent']) ? false : true,
            ];
            if (!empty($rooms[$rate['room_category']])) {
                $rates[$rate['id']]['title'] .= '<br><small>' . $rooms[$rate['room_category']] . '</small>';
            }
        }

        return $rates;
    }

    /**
     * @param array $response
     * @throws Exception
     */
    private function checkErrors($response)
    {
        if (!empty($response['error'])) {
            throw new Exception(
                is_array($response['error']) ? http_build_query($response['error']) : $response['error']
            );
        };
    }

    /**
     * {@inheritDoc}
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $data = ['hotel' => $config->getHotelId()];
        $rooms = [];

        try {
            $room_categories = $this->apiBrowser->getRoomCategories($data);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log($e->getResponse()->getBody()->getContents(), 'error');
            $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
        }



        foreach ($room_categories as $room_category) {
            $rooms[$room_category['id']] = $room_category['name'];
        }

        return $rooms;
    }

    /**
     * @param ChannelManagerConfigInterface $config
     */
    public function syncServices(ChannelManagerConfigInterface $config)
    {
        /** @var OstrovokConfig $config */
        $config->removeAllServices();
        foreach (self::SERVICES as $serviceKey => $serviceName) {
            $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy(
                [
                    'code' => $serviceName,
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
    }

    private function getRatePlansArray($hotelId): array
    {
        $result = [];
        $ratePlans = [];
        try {
            $ratePlans = $this->apiBrowser->getRatePlans(['hotel' => $hotelId]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log($e->getResponse()->getBody()->getContents(), 'error');
            $this->notifyError(self::CHANNEL_MANAGER_TYPE, $e->getResponse()->getBody()->getContents());
        }

        foreach ($ratePlans as $ratePlan) {
            $result[$ratePlan['id']] = $ratePlan;
        }

        return $result;
    }

    /**
     * @param array $rna_request_data
     * @param string $action
     * @return bool
     */
    private function sendApiRequest(array $rna_request_data, string $action): bool
    {
        $result = true;
        if (\count($rna_request_data)) {
            try {
                $response = $this->apiBrowser->updateRNA($rna_request_data);
                $this->log('Ostrovok ' . $action . ' success. ' . json_encode($response));
            } catch (OstrovokApiServiceException $e) {
                $result = false;
                $this->log('Ostrovok ' . $action . ' failed. ' . $e->getMessage());
            }
        } else {
            $this->log('Ostrovok ' . $action . ' empty $rna_request_data!', 'alert');
        }

        return $result;
    }
}
