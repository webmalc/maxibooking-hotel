<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

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
            $priceCaches = $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetchQueryBuilder(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [$airbnbTariff->getId()],
                true
            )->getQuery()->execute();

            $pricePeriods = [];

            foreach ($priceCaches as $priceCache) {
                /** @var PriceCache $priceCache */
                // Listing в airbnb эквивалентен типу номера
                $airbnbListingInfo = $roomTypes[$priceCache->getRoomType()->getId()];
                if ($airbnbListingInfo === null) {
                    continue;
                }
                $listingId = $airbnbListingInfo['syncId'];
                /** @var PricePeriod $currentPricePeriod */
                $currentPricePeriod = $pricePeriods[$airbnbListingInfo];
                if ($currentPricePeriod != null) {
                    $currentPricePeriod->increaseEndDate();
                } else {
                    $pricePeriods[$listingId] = new PricePeriod(
                        $priceCache->getRoomType(),
                        $priceCache->getPrice(),
                        $priceCache->getDate(),
                        $priceCache->getDate()
                    );
                }
            }

            foreach ($pricePeriods as $listingId => $pricePeriod) {
                /** @var AirbnbRequestFormatter $requestFormatter */
                $requestFormatter = $this->getRequestFormatter($config);
                $requestInfo = $requestFormatter->formatUpdatePricesRequest($pricePeriod, $listingId);
                $response = $this->sendRequestAndGetJsonResponse($requestInfo);

                $result = $this->checkResponse($response);

                $this->log($response);
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
            $restrictions = $this->dm->getRepository('MBHPriceBundle:Restriction')->fetchQueryBuilder(
                $begin,
                $end,
                $config->getHotel(),
                $this->getRoomTypeArray($roomType),
                [$airbnbTariff->getId()]
            )->getQuery()->execute();

            $restrictionPeriods = [];

            foreach ($restrictions as $restriction) {
                /** @var Restriction $restriction */
                // Listing в airbnb эквивалентен типу номера
                $airbnbListingInfo = $roomTypes[$restriction->getRoomType()->getId()];
                if ($airbnbListingInfo === null) {
                    continue;
                }
                $listingId = $airbnbListingInfo['syncId'];
                /** @var PricePeriod $currentPricePeriod */
                $currentPricePeriod = $restrictionPeriods[$airbnbListingInfo];
                if ($currentPricePeriod != null) {
                    $currentPricePeriod->increaseEndDate();
                } else {
                    $pricePeriods[$listingId] = new ClosedPeriod(
                        $restriction->getRoomType(),
                        $restriction->getClosed() ? 1 : 0,
                        \DateTime::createFromFormat('Y-m-d',$restriction->getDate()),
                        \DateTime::createFromFormat('Y-m-d',$restriction->getDate())
                    );
                }
            }

            foreach ($restrictionPeriods as $listingId => $restrictionPeriod) {
                /** @var AirbnbRequestFormatter $requestFormatter */
                $requestFormatter = $this->getRequestFormatter($config);
                $requestInfo = $requestFormatter->formatUpdateAvailabilityRequest($restrictionPeriod, $listingId);
                $response = $this->sendRequestAndGetJsonResponse($requestInfo);

                $result = $this->checkResponse($response);

                $this->log($response);
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
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        $requestInfo = $this->getRequestFormatter($config)->formatGetListingsRequests();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);

        $rooms = [];
        if ($response['metadata']['listing_count'] > 0) {
            foreach ($response['listings'] as $roomType) {
                $rooms[$roomType['id']] = $roomType['listing_descriptions'][0]['name'];
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
        $requestFormatter = $this->getRequestFormatter($config);
        $requestInfo = $requestFormatter->formatGetListingsRequests();
        $response = $this->sendRequestAndGetJsonResponse($requestInfo);

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

    public function testRequest()
    {
        $param = $this->send('https://api.airbnb.com/v1/authorize', ['password' => '44834631TRye2009', 'client_id' => '3092nxybyb0otqw18e8nh5nty', 'username'=> 'faainttt@gmail.com'], null, true, 'POST');
        $ar = 123;
        return $param;
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
            $requestInfo->getRequestData(),
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