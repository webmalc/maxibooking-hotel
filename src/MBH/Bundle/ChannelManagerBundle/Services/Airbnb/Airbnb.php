<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Model\Airbnb\ClosedPeriod;
use MBH\Bundle\ChannelManagerBundle\Model\Airbnb\PricePeriod;
use MBH\Bundle\ChannelManagerBundle\Model\RequestInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Airbnb extends AbstractChannelManagerService
{
    /**
     * Config class
     */
    const CONFIG = 'AirbnbConfig';

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
            /** @var AirbnbConfig $config */
            $configTariffs = $config->getTariffs();
            if (count($configTariffs) === 0) {
                //TODO: Здесь бы экспешен кинуть
                continue;
            }
            //В airbnb есть только один тариф
            /** @var Tariff $airbnbTariff */
            $airbnbTariff = $configTariffs[0]->getTariff();

            //$roomTypes array[roomId => [roomId('syncId'), RoomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //$priceCaches array [roomTypeId][tariffId][date => PriceCache]
            $priceCachesByRoomTypes = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [$airbnbTariff->getId()],
                true
            );
            $pricePeriods = [];

            foreach ($priceCachesByRoomTypes as $roomTypeId => $priceCachesByRoomType) {
                $priceCachesByDates = current($priceCachesByRoomType);
                /** @var PricePeriod $currentPricePeriod */
                $currentPricePeriod = null;
                $listingId = $roomTypes[$roomTypeId]['syncId'];
                foreach ($priceCachesByDates as $date => $priceCache) {
                    /** @var PriceCache $priceCache */
                    $date = \DateTime::createFromFormat('d.m.Y', $date);
                    if ($currentPricePeriod
                        && date_diff($currentPricePeriod->getEndDate(), $date)->d == 1
                        && $priceCache === $currentPricePeriod->getPrice() ) {
                        $currentPricePeriod->increaseEndDate();
                    } else {
                        $currentPricePeriod = new PricePeriod(
                            $priceCache->getRoomType(),
                            $priceCache->getPrice(),
                            $priceCache->getDate(),
                            $listingId
                        );
                        $pricePeriods[] = $currentPricePeriod;
                    }
                }
            }

            foreach ($pricePeriods as $pricePeriod) {
                /** @var AirbnbRequestFormatter $requestFormatter */
                $requestFormatter = $this->getRequestFormatter($config);
                $requestInfo = $requestFormatter->formatUpdatePricesRequest($pricePeriod);
                $response = $this->sendRequestAndGetJsonResponse($requestInfo);

                $result = $this->checkResponse($response);

                $this->log(serialize($response));
            }
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
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            /** @var AirbnbConfig $config */
            /** @var AirbnbRequestFormatter $requestFormatter */
            $requestFormatter = $this->getRequestFormatter($config);

            $configTariffs = $config->getTariffs();
            if (count($configTariffs) === 0) {
                //TODO: Здесь бы экспешен кинуть
                continue;
            }
            //В airbnb есть только один тариф
            /** @var Tariff $airbnbTariff */
            $airbnbTariff = $configTariffs[0]->getTariff();

            //$roomTypes array[roomTypeId => [roomId('syncId'), roomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //array[roomTypeId][tariffId][date('d.m.Y') => RoomCache]
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetchQueryBuilder(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                [$airbnbTariff->getId()]
            )->getQuery->execute();
            //TODO: Необходимо получить данные

//            $result = $this->checkResponse($sendResult);
//
//            $this->log($sendResult);
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
            /** @var AirbnbConfig $config */
            $configTariffs = $config->getTariffs();
            if (count($configTariffs) === 0) {
                //TODO: Здесь бы экспешен кинуть
                continue;
            }
            //В airbnb есть только один тариф
            /** @var Tariff $airbnbTariff */
            $airbnbTariff = $configTariffs[0]->getTariff();

            //$roomTypes array[roomId => [roomId('syncId'), RoomType('doc')]]
            $roomTypes = $this->getRoomTypes($config);
            //$priceCaches array [roomTypeId][tariffId][date => PriceCache]
            $restrictionsByRoomTypes = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [$airbnbTariff->getId()],
                true
            );

            $restrictionPeriods = [];

            foreach ($restrictionsByRoomTypes as $roomTypeId => $restrictionsByRoomType) {
                $restrictionsByDates = current($restrictionsByRoomType);
                /** @var ClosedPeriod $currentClosedPeriod */
                $currentClosedPeriod = null;
                $listingId = $roomTypes[$roomTypeId]['syncId'];
                foreach ($restrictionsByDates as $date => $restriction) {
                    /** @var Restriction $restriction */
                    $date = \DateTime::createFromFormat('d.m.Y', $date);
                    if ($currentClosedPeriod
                        && date_diff($currentClosedPeriod->getEndDate(), $date)->d == 1
                        && $restriction->getClosed() === $currentClosedPeriod->getIsClosed()) {
                        $currentClosedPeriod->increaseEndDate();
                    } else {
                        $currentClosedPeriod = new ClosedPeriod(
                            $restriction->getRoomType(),
                            $restriction->getClosed(),
                            $restriction->getDate(),
                            $listingId
                        );
                        $restrictionPeriods[] = $currentClosedPeriod;
                    }
                }
            }

            foreach ($restrictionPeriods as $restrictionPeriod) {
                /** @var AirbnbRequestFormatter $requestFormatter */
                $requestFormatter = $this->getRequestFormatter($config);
                $requestInfo = $requestFormatter->formatUpdateAvailabilityRequest($restrictionPeriod);
                $response = $this->sendRequestAndGetJsonResponse($requestInfo);

                $result = $this->checkResponse($response);

                $this->log(serialize($response));
            }
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
     * @throws Exception
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $requestInfo = $this->getRequestFormatter($config)->formatGetListingsRequests();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);
        if (!$this->checkResponse($response)) {
            throw new Exception($response['error_message']);
        }
        $rooms = [];
        if ($response['metadata']['listing_count'] > 0) {
            foreach ($response['listings'] as $roomType) {
                if (count($roomType['listing_descriptions']) > 0) {
                    $rooms[$roomType['id']] = $roomType['listing_descriptions'][0]['name'];
                }
            }
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
        $airbnbTariffTitle = $this->container->get('translator')->trans('form.airbnb.tariffTitle.label');
        $roomTypeIds = [];
        foreach ($config->getRooms() as $room) {
            /** @var Room $room */
            $roomTypeIds[] = $room->getRoomId();
        }
        $result[] = ['title' => $airbnbTariffTitle, 'rooms' => $roomTypeIds];

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
        if (isset($response['error_code'])) {
            return false;
        }
        return true;
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        $requestFormatter = $this->getRequestFormatter($config);
        $roomTypes = $config->getRooms();
        foreach ($roomTypes as $roomType) {
            /** @var Room $roomType */
            $requestInfo = $requestFormatter->formatDeactivateListingRequest($roomType->getRoomId());
            $response = $this->sendRequestAndGetJsonResponse($requestInfo);
            $result = $this->checkResponse($response);

            $this->log($response);

            if (!$result) {
                return $result;
            }
            $this->log($response);
        }
        return true;
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


    public function safeConfigDataAndGetErrorMessage($email, $password, AirbnbConfig $config)
    {
        $errorMessage = $this->setAccessTokenAndGetErrorMessage($email, $password, $config);
        if ($errorMessage !== '') {
            return $errorMessage;
        }
        $errorMessage = $this->setUserIdAndGetErrorMessage($config);
        if ($errorMessage !== '') {
            return $errorMessage;
        }
        return '';
    }

    private function setAccessTokenAndGetErrorMessage($email, $password, AirbnbConfig $config)
    {
        $requestInfo = $this->getRequestFormatter($config)->formatAuthorizeRequest($email, $password);
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);
        if (!$this->checkResponse($response)) {
            return $this->getResponseErrorMessage($response);
        }
        $config->setAccessToken($response['access_token']);
        return '';
    }

    private function setUserIdAndGetErrorMessage(AirbnbConfig $config) : string
    {
        $requestInfo = $this->getRequestFormatter($config)->formatGetUserInfoRequest();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);

        if (!$this->checkResponse($response)) {
            return $this->getResponseErrorMessage($response);
        }
        $config->setHotelId($response['user']['user']['id']);

        return '';
    }

    /**
     * @param $config
     * @return AirbnbRequestFormatter $this
     */
    private function getRequestFormatter($config)
    {
        return $this->container->get('mbh.channelmanager.airbnb_request_formatter')->setInitData($config);
    }

    private function sendRequestAndGetJsonResponse(RequestInfo $requestInfo)
    {
        $jsonResponse = $this->send(
            $requestInfo->getUrl(),
            json_encode($requestInfo->getRequestData()),
            $requestInfo->getHeadersList(),
            true,
            $requestInfo->getMethodName());

        return json_decode($jsonResponse, true);
    }

    private function getResponseErrorMessage($response)
    {
        return $response['error_message'];
    }
}