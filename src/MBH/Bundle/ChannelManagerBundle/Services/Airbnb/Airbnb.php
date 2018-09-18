<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use GuzzleHttp\Client;
use ICal\ICal;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class Airbnb extends AbstractChannelManagerService
{
    const NAME = 'airbnb';
    const SYNC_URL_BEGIN = 'https://www.airbnb.ru/calendar/ical/';
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
        return true;
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
        return true;
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
        return true;
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
        $client = new Client();
        /** @var AirbnbConfig $config */
        foreach ($this->getConfig() as $config) {
            $roomTypes = array_map(function(AirbnbRoom $room) {
                return $room->getRoomType();
            }, $config->getRooms()->toArray());
            $packages = $this->dm
                ->getRepository('MBHPackageBundle:Package')
                ->findBy([
                    'roomType' => ['$in' => $roomTypes],
                    'channelManagerType' => self::NAME
                ]);
            $packagesByRoomIds = [];
            foreach ($packages as $package) {
                $packagesByRoomIds[$package->getRoomType()->getId()][$package->getChannelManagerId()] = $package;
            }

            foreach ($config->getRooms() as $room) {
                $response = $client->get($room->getSyncUrl());
                if ($response->getStatusCode() === 200) {
                    $iCalResponse = new ICal((string)$response->getBody());
                    $events = $iCalResponse->cal['VEVENT'];
                } else {
                    $this->log((string)$response->getBody());
                }
            }
        }

        return $this;
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {
        return [];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        return [];
    }

    /**
     * Check response from booking service
     * @param mixed $response
     * @param array $params
     * @return boolean
     */
    public function checkResponse($response, array $params = null)
    {
        // TODO: Implement checkResponse() method.
    }

    /**
     * Close sales on service
     * @param ChannelManagerConfigInterface $config
     * @return boolean
     */
    public function closeForConfig(ChannelManagerConfigInterface $config)
    {
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function pushResponse(Request $request)
    {
        return new Response();
    }
}