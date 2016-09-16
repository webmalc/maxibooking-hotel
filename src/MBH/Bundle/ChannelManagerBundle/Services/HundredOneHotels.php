<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\HundredOneHotelsConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\Response;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\HttpFoundation\Request;

class HundredOneHotels extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'HundredOneHotelsConfig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://101hotels.info/api2/';

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
            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */
                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var RoomCache $currentDateRoomCache */
                        $currentDateRoomCache = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    } else {
                        $roomQuotaForCurrentDate = 0;
                    }
                    // $quotasData array [date][roomId => roomQuota]
                    $quotasData[$day->format('Y-m-d')][$roomTypeId] = $roomQuotaForCurrentDate;
                }
            }

            if (!isset($quotasData)) {
                continue;
            }

            $request = $this->templating->render(
                '@MBHChannelManager/HundredOneHotels/updateRooms.json.twig',
                [
                    'config' => $config,
                    'quotas' => $quotasData,
                ]
            );

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
            /** @var HundredOneHotelsConfig $config */
            //$roomTypes array[roomTypeId => [roomId('syncId'), roomType('doc')]]
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

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */
                    if (isset($priceCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                        /** @var PriceCache $currentDateRoomCache */
                        $currentDateRoomCache = $priceCaches[$roomTypeId][0][$day->format('d.m.Y')];
                        $roomQuotaForCurrentDate = $currentDateRoomCache->getLeftRooms() > 0 ? $currentDateRoomCache->getLeftRooms() : 0;
                    } else {
                        $roomQuotaForCurrentDate = 0;
                    }
                    // $quotasData array [date][roomId][roomQuota]
                    $quotasData[$day->format('Y-m-d')][$roomTypeId] = $roomQuotaForCurrentDate;
                }
            }

            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */
                    foreach ($tariffs as $tariffId => $tariff) {

                        if (!isset($serviceTariffs[$tariff['syncId']]) || $serviceTariffs[$tariff['syncId']]['readonly'] || $serviceTariffs[$tariff['syncId']]['is_child_rate']) {
                            continue;
                        }

                        if (!empty($serviceTariffs[$tariff['syncId']]['rooms']) && !in_array($roomTypeInfo['syncId'], $serviceTariffs[$tariff['syncId']]['rooms'])) {
                            continue;
                        }
                        if (isset($priceCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
                            /** @var PriceCache $currentDatePriceCache */
                            $currentDatePriceCache = $priceCaches[$roomTypeId][0][$day->format('d.m.Y')];
                            $roomQuotaForCurrentDate = $currentDatePriceCache->getSinglePrice() > 0 ? $currentDatePriceCache->getLeftRooms() : 0;
                        } else {
                            $roomQuotaForCurrentDate = 0;
                        }
                        //$quotasData array [date][roomId][roomQuota]
                        $quotasData[$day->format('Y-m-d')][$roomTypeId][$roomTypeInfo['syncId']][] = $roomQuotaForCurrentDate;
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
                    'data' => $data,
                ]
            );

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

            $data = [];
            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
                    /** @var \DateTime $day */
                    foreach ($tariffs as $tariffId => $tariff) {

                        if (!isset($serviceTariffs[$tariff['syncId']]) || $serviceTariffs[$tariff['syncId']]['readonly'] || $serviceTariffs[$tariff['syncId']]['is_child_rate']) {
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
                            /** @var Restriction $restriction */
                            $restriction = $restrictions[$roomTypeId][$tariffId][$day->format('d.m.Y')];
                            $data[$day->format('Y-m-d')][] =
                            $data[$day->format('Y-m-d')][$roomTypeInfo['syncId']] = [
                                'minimumstay' => (int)$restriction->getMinStay(),
                                'closedonarrival' => $restriction->getClosedOnArrival() ? 1 : 0,
                                'closedondeparture' => $restriction->getClosedOnDeparture() ? 1 : 0,
                                'closed' => $restriction->getClosed() || !$price ? 1 : 0,
                            ];
                        } else {
                            $data[$day->format('Y-m-d')][$roomTypeInfo['syncId']] = [
                                'minimumstay' => 0,
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
                'MBHChannelManagerBundle:HundredOneHotels:updateRestrictions.json.twig',
                [
                    'config' => $config,
                    'restrictions' => $data,
                ]
            );
            $sendResult = $this->send(static::BASE_URL, $request, null, true);

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
        // TODO: Implement createPackages() method.
    }

    /**
     * Pull orders from service server
     * @return mixed
     */
    public function pullOrders()
    {
        // TODO: Implement pullOrders() method.
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement pullRooms() method.
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        //В данном сервисе не хранятся данные о тарифах
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
        json_decode($response);

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
        // TODO: Implement closeForConfig() method.
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        // TODO: Implement pushResponse() method.
    }
}