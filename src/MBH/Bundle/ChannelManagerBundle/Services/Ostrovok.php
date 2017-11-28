<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
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
        $rna_request_data = [];
        //Закрыли на год вперед комнаты
        $rooms = $this->pullRooms($config);
        $startDate = new \DateTime("now");
        $endDate = (clone $startDate)->modify("+1 year");
        $hotelId = $config->getHotelId();
        foreach ($rooms as $roomId => $roomName) {
            $rna_request_data = array_merge_recursive(
                $rna_request_data,
                $this->dataGenerator->getRequestDataRnaRoomAmount($roomId, 0, $startDate, $endDate, $hotelId)
            );
        }
        //Цены
        $rate_plans = $this->apiBrowser->getRatePlans(['hotel' => $hotelId]);
        foreach ($rate_plans as $rate_plan) {
            if ($rate_plan['parent']) {
                continue;
            }
            if (count($rate_plan['possible_occupancies'])) {
                foreach ($rate_plan['possible_occupancies'] as $occupancyId) {
                    $rna_request_data = array_merge_recursive(
                        $rna_request_data,
                        $this->dataGenerator->getRequestDataRnaPrice(
                            $occupancyId,
                            $rate_plan['room_category'],
                            $rate_plan['id'],
                            0,
                            $startDate,
                            $endDate,
                            $hotelId
                        )
                    );
                }
            }
        }

        //Ограничения
        foreach ($rate_plans as $rate_plan) {
            $rna_request_data = array_merge_recursive(
                $rna_request_data,
                $this->dataGenerator->getRequestDataRnaRestrictions(
                    $rate_plan['room_category'],
                    $rate_plan['id'],
                    $config->getHotelId(),
                    $startDate,
                    $endDate,
                    1
                )
            );
        }

        return $this->sendApiRequest($rna_request_data, __METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        $rna_request_data = [];
        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var ChannelManagerConfigInterface $config */
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
                        /** @var RoomCache $info */
                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $leftRooms = $info->getLeftRooms() ?: 0;
                        $rna_request_data = array_merge_recursive(
                            $rna_request_data,
                            $this->dataGenerator->getRequestDataRnaRoomAmount(
                                $roomTypeInfo['syncId'],
                                $leftRooms,
                                $day,
                                $day,
                                $config->getHotelId()
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
    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        $rna_request_data = [];
        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
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

            $octrovokRoomTypes = $this->getRoomTypes($config, true);
            $ostrovokTariffs = $this->getTariffs($config, true);
            $ostrovokRatePlans = $this->getRatePlansArray($config->getHotelId());
            $occupancies = $this->apiBrowser->getOccupancies(['hotel' => $config->getHotelId()], true);

            $serviceTariffs = $this->pullTariffs($config);


            foreach ($octrovokRoomTypes as $ostrovokRoomTypeId => $roomTypeInfo) {
                $roomType = $roomTypeInfo['doc'];
                /** @var RoomType $roomType */
                $roomTypeId = $roomType->getId();
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    foreach ($ostrovokTariffs as $ostrovokTariffId => $tariffInfo) {
                        /** @var Tariff $tariff */
                        if ($serviceTariffs[$ostrovokTariffId]['is_child_rate']) {
                            continue;
                        }
                        /** @var \MBH\Bundle\PriceBundle\Document\Tariff $tariff */
                        $tariff = $tariffInfo['doc'];
                        $tariffId = $tariff->getId();

                        if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                            foreach ($ostrovokRatePlans[$ostrovokTariffId]['possible_occupancies'] as $occupancyId) {
                                if ($occupancies[$occupancyId]['room_category'] !== $ostrovokRoomTypeId) {
                                    continue;
                                }
                                $adults = $occupancies[$occupancyId]['capacity'];
                                $children = 0;
                                $price = $this->calculation->calcPrices(
                                    $roomType,
                                    $tariff,
                                    $day,
                                    $day,
                                    $adults,
                                    $children,
                                    null,
                                    false
                                );
                                $price = $price[$adults.'_'.$children]['total']??null;
                                if (!$price) {
                                    $message = $this->container->get('translator')->trans('services.ostrovok.error_getting_price_for_number', [
                                        '%roomTypeName%' => $roomType->getFullTitle(),
                                        '%numberOfAdults%' => $adults
                                    ]);
                                    $this->log('Error! '.$message);
                                    $result = false;
                                    continue;
                                    /*throw new \Exception($message);*/
                                }
                                $rna_request_data = array_merge_recursive(
                                    $rna_request_data,
                                    $this->dataGenerator->getRequestDataRnaPrice(
                                        $occupancyId,
                                        $ostrovokRoomTypeId,
                                        $ostrovokTariffId,
                                        $price,
                                        $day,
                                        $day,
                                        $config->getHotelId()
                                    )
                                );
                            }
                        } else {
                            foreach ($ostrovokRatePlans[$ostrovokTariffId]['possible_occupancies'] as $occupancyId) {
                                if ($occupancies[$occupancyId]['room_category'] !== $ostrovokRoomTypeId) {
                                    continue;
                                }
                                $price = 0;
                                $rna_request_data = array_merge_recursive(
                                    $rna_request_data,
                                    $this->dataGenerator->getRequestDataRnaPrice(
                                        $occupancyId,
                                        $ostrovokRoomTypeId,
                                        $ostrovokTariffId,
                                        $price,
                                        $day,
                                        $day,
                                        $config->getHotelId()
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        return $this->sendApiRequest($rna_request_data, __METHOD__) && $result;
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
                    $this->log('Не удалось обновить ограничения для тарифов'.$exception->getMessage());
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
                        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
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
        $date = (new \DateTime('now midnight'))->format('Y-m-d');
        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            $bookings = $this->apiBrowser->getBookings([
                'hotel' => $config->getHotelId(),
                'modified_at_start_at' => $date
            ]);
            $this->log('There are '.count($bookings).' total '.$date);
            if (!$bookings) {
                continue;
            }

            foreach ($bookings as $reservation) {
                $reservationCreatedAt = new \DateTime($reservation['created_at']);
                $now = new \DateTime('midnight');
                if ($reservationCreatedAt < $now) {
                    continue;
                }

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
                    $this->log('Order '.$order->getId().'was created.');
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
                        $this->log('Order '.$order->getId().'was changed.');
                    }
                }
                //If Cancelled
                if ((string)$reservation['status'] === 'cancelled' && $order) {
                    $order->setChannelManagerStatus('cancelled');
                    $order->setChannelManagerEditDateTime($reservation['modified_at']);
                    $this->dm->persist($order);
                    $this->dm->flush();
                    $this->notify($order, 'ostrovok', 'delete');
                    $this->dm->remove($order);
                    $this->dm->flush();
                    $this->log('Order '.$order->getId().'was cancelled.');
                    $result = true;
                }

                if (($reservation['status'] === 'cancelled' || $isModified) && !$order) {
                    $this->notifyError(
                        'ostrovok',
                        '#'.$reservation['uuid'].' '.
                        $reservation['last_name'].' '.$reservation['first_name']
                    );
                    $this->log('Error! Бронь существует, но нет в базе. '.$reservation['uuid']);
                }
            }
        }

        return $result;
    }


    private function createPackage(array $reservation, ChannelManagerConfigInterface $config, Order $order = null)
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
            $reservation['email']??null
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
            $errorMessage = 'ERROR: invalid roomType #'.(string)$reservation['room_category'];
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
                $errorMessage .= 'ERROR: Not mapped rate <'.$tariff->getName();

                if (!$tariff) {
                    continue;
                }
            }

            $total += (float)$price;
            $date = (new \DateTime($reservation['arrive_at']))->modify('+ '.$dayIndex.' days');
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
        $rate_plans = $this->apiBrowser->getRatePlans(['hotel' => $config->getHotelId()]);
        $rooms = $this->pullRooms($config);

        $rates = [];
        foreach ($rate_plans as $rate) {
            $rates[$rate['id']] = [
                'title' => $rate['name'],
                'readonly' => false,
                'is_child_rate' => empty($rate['parent']) ? false : true,
            ];
            if (!empty($rooms[$rate['room_category']])) {
                $rates[$rate['id']]['title'] .= '<br><small>'.$rooms[$rate['room_category']].'</small>';
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
        $room_categories = $this->apiBrowser->getRoomCategories($data);

        $rooms = [];
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

    private function getRatePlansArray($hotelId)
    {
        $result = [];
        $ratePlans = $this->apiBrowser->getRatePlans(['hotel' => $hotelId]);
        foreach ($ratePlans as $ratePlan) {
            $result[$ratePlan['id']] = $ratePlan;
        }

        return $result;
    }

    private function sendApiRequest(array $rna_request_data, string $action)
    {

        try {
            if (count($rna_request_data)) {
                $this->apiBrowser->updateRNA($rna_request_data);
                $result = true;
                $this->log('Ostrovok '.$action.' success');
            } else {
                $result = true;
                $this->log('Ostrovok '.$action.' empty $rna_request_data!');
            }
        } catch (OstrovokApiServiceException $exception) {
            $result = false;
            $this->log('Ostrovok '.$action.' error '.$exception->getMessage());
            $this->logger->addAlert($action.' error. ', $rna_request_data);
        }

        return $result;
    }
}
