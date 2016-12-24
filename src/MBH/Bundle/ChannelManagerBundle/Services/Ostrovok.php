<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiServiceException;
use MBH\Bundle\PriceBundle\Document\PriceCache;
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
        10 => 'Full pansion'

    ];

    /**
     * @var array
     */
    private $params;

    private $apiBrowser;

    private $calculation;

    /**
     * @var string
     */
    private $url = self::URL;

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
            $rna_request_data = array_merge_recursive($rna_request_data, $this->dataGenerator->getRequestDataRnaRoomAmount($roomId, 0, $startDate, $endDate, $hotelId));
        }

        $rate_plans = $this->apiBrowser->getRatePlans(['hotel' => $hotelId]);
        foreach ($rate_plans as $rate_plan) {
            if ($rate_plan['parent']) continue;
            if (count($rate_plan['possible_occupancies'])) {
                foreach ($rate_plan['possible_occupancies'] as $occupancyId) {
                    $rna_request_data = array_merge_recursive($rna_request_data, $this->dataGenerator->getRequestDataRnaPrice($occupancyId, $rate_plan['room_category'], $rate_plan['id'], 0, $startDate, $endDate, $hotelId));
                }
            }

        }

        $result = true;
        try {
            $this->apiBrowser->updateRNA($rna_request_data);
            $this->log('Ostrovok was closed config');
        } catch (OstrovokApiServiceException $exception) {
            $result = false;
        }

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
                        $rna_request_data = array_merge_recursive($rna_request_data, $this->dataGenerator->getRequestDataRnaRoomAmount($roomTypeInfo['syncId'], $leftRooms, $day, $day, $config->getHotelId()));
                    }
                }
            }

            if (empty($rna_request_data)) {
                continue;
            }

        }
        try {
            $this->apiBrowser->updateRNA($rna_request_data);
            $this->log('Ostrovok was updatedRooms');
        } catch (OstrovokApiServiceException $exception) {
            $result = false;
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
        $rna_request_data = [];
        foreach ($this->getConfig() as $config) {
            /** @var ChannelManagerConfigInterface $config */
            $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [],
                true,
                $this->roomManager->useCategories
            );

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
                        if ($serviceTariffs[$ostrovokTariffId]['is_child_rate']) continue;
                        $tariff = $tariffInfo['doc'];
                        $tariffId = $tariff->getId();

                        if (isset($priceCaches[$roomTypeId][$tariffId][$day->format('d.m.Y')])) {
                            foreach ($ostrovokRatePlans[$ostrovokTariffId]['possible_occupancies'] as $occupancyId) {
                                if ($occupancies[$occupancyId]['room_category'] !== $ostrovokRoomTypeId) continue;
                                $adults = $occupancies[$occupancyId]['capacity'];
                                $children = 0;
                                $price = $this->calculation->calcPrices($roomType, $tariff, $day, $day, $adults, $children, null, false);
                                $price = $price[$adults . '_' . $children]['total'];
                                $rna_request_data = array_merge_recursive($rna_request_data, $this->dataGenerator->getRequestDataRnaPrice($occupancyId, $ostrovokRoomTypeId, $ostrovokTariffId, $price, $day, $day, $config->getHotelId()));
                            }

                        } else {
                            foreach ($ostrovokRatePlans[$ostrovokTariffId]['possible_occupancies'] as $occupancyId) {
                                if ($occupancies[$occupancyId]['room_category'] !== $ostrovokRoomTypeId) continue;
                                $price = 0;
                                $rna_request_data = array_merge_recursive($rna_request_data, $this->dataGenerator->getRequestDataRnaPrice($occupancyId, $ostrovokRoomTypeId, $ostrovokTariffId, $price, $day, $day, $config->getHotelId()));
                            }
                        }
                    }

                }

            }
        }
        try {
            $this->apiBrowser->updateRNA($rna_request_data);
            $this->log('Ostrovok wal updated Prices');
        } catch (OstrovokApiServiceException $exception) {
            $result = false;
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


            $configRoomTypes = $this->getRoomTypes($config);
            $configTarrifs = $this->getTariffs($config);
            $roomTypeRestrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : array_keys($configRoomTypes),
                array_keys($configTarrifs),
                true
            );

            foreach ($roomTypeRestrictions as $roomId => $tariffRestrictions) {
                $ostrovokRoomTypeId = $configRoomTypes[$roomId]['syncId'];
                foreach ($tariffRestrictions as $tarifId => $roomTypeRestrictions) {
                    $accordingOstrovokTariffId = $this->getAccordingTariff($ostrovokRoomTypeId, $tarifId, $config);
                    if (!$accordingOstrovokTariffId) continue;
                    foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                        if (in_array($day->format('d.m.Y'), array_keys($roomTypeRestrictions))) {
                            /** @var Restriction $restriction */
                            $restriction = $roomTypeRestrictions[$day->format('d.m.Y')];
                            $rna_request_data = array_merge_recursive(
                                $rna_request_data,
                                $this->dataGenerator->getRequestDataRnaRestrictions(
                                    $ostrovokRoomTypeId,
                                    $accordingOstrovokTariffId,
                                    $config->getHotelId(),
                                    $day,
                                    $day,
                                    (int)$restriction->getMinStayArrival() ?: null,
                                    (int)$restriction->getMaxStayArrival() ?: null,
                                    (int)$restriction->getMinStay() ?: null,
                                    (int)$restriction->getMaxStay() ?: null,
                                    (bool)$restriction->getClosedOnArrival() || (bool)$restriction->getClosed(),
                                    (bool)$restriction->getClosedOnDeparture() || (bool)$restriction->getClosed()
                                )
                            );
                        } else {
                            $rna_request_data = array_merge_recursive(
                                $rna_request_data,
                                $this->dataGenerator->getRequestDataRnaRestrictions(
                                    $ostrovokRoomTypeId,
                                    $accordingOstrovokTariffId,
                                    $config->getHotelId(),
                                    $day,
                                    $day
                                )
                            );
                        }
                    }
                }
            }


        }
        if (!$rna_request_data) return false;
        try {
            $this->apiBrowser->updateRNA($rna_request_data);
            $answer = 'Ostrovok was updated Restrictions';
        } catch (OstrovokApiServiceException $exception) {
            $answer = $exception->getMessage();
            $result = false;
        }
        $this->log($answer);

        return $result;
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
        /** @var ChannelManagerConfigInterface $config */
        foreach ($this->getConfig() as $config) {
            $orders = $this->apiBrowser->getBookings(['hotel' => $config->getHotelId()]);
            $this->log('There are ' . count($orders) . ' total ');
            //TODO: Подумать что сделать на случай если нет заказов.
            if (!$orders) continue;
            foreach ($orders as $order) {

            }
        }

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
        $config->removeAllServices();
        foreach (self::SERVICES as $serviceKey => $serviceName) {
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

    private function getAccordingTariff($ostrovokRoomType, $tariffId, ChannelManagerConfigInterface $config)
    {
        $ostrovorTariffs = $this->apiBrowser->getRatePlans(['hotel' => $config->getHotelId()]);

        $roomTypeTariffs = array_filter($ostrovorTariffs, function ($tariff) use ($ostrovokRoomType) {
            return $tariff['room_category'] == $ostrovokRoomType && !$tariff['parent'];
        });
        if (!count($roomTypeTariffs)) {
            return null;
        }
        $tariffIds = [];
        foreach ($roomTypeTariffs as $roomTypeTariff) {
            $tariffIds[] = $roomTypeTariff['id'];
        }

        if (!count($tariffIds)) {
            throw new OstrovokApiServiceException('Не могу найти ID тарифов в сопоставлении комнаты. Метод getAccordingTariff');
        }

        $configTariffs = $this->getTariffs($config, true);
        if (!count($configTariffs)) {
            throw new OstrovokApiServiceException('Не существует тарифов в конфигурации островка!');
        }
        foreach ($tariffIds as $id) {
            if (isset($configTariffs[$id]) && $configTariffs[$id]['doc']->getId() == $tariffId) {
                return $id;
            }
        }
    }

}
