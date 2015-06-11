<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

/**
 *  ChannelManager service
 */
class Oktogo
{
    /**
     * Config class
     */
    const CONFIG = 'OktogoConfig';

    /**
     * Dev or test mode
     */
    const TEST = true;

    /**
     * Get roomTypes & tariffs template file
     */
    const GET_ROOMS_TARIFFS_TEMPLATE = 'MBHChannelManagerBundle:Oktogo:get.xml.twig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://hotelapi-release.oktogotest.ru/';

    private $headers = [
        'Content-Type: text/xml; charset=utf-8',
        'Accept: text/xml',
        'Cache-Control: no-cache',
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * {@inheritDoc}
     */
    public function pullOrders()
    {

    }

    public function closeAll() {

    }

    public function sync() {

    }

    public function checkResponse($response, array $params = null) {

    }

    public function updatePrices(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null){

    }
    public function updateRooms(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null){

    }
    public function updateRestrictions(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null){

    }

    /**
     * @param OktogoConfig $config
     * @return bool
     */
    public function roomSync(OktogoConfig $config)
    {
        $sendResult = $this->send(
            static::BASE_URL . 'rooms',
            $this->templating->render(static::GET_ROOMS_TARIFFS_TEMPLATE, ['config' => $config]),
            $this->getHeaders($config),
            true
        );

        $xml = simplexml_load_string($sendResult);

        if (!$xml instanceof \SimpleXMLElement) {
            return false;
        }

        $roomTypes = $config->getHotel()->getRoomTypes();
        $config->removeAllRooms();

        foreach ($xml->children() as $room) {

            foreach ($roomTypes as $roomType) {
                if ($roomType->getFullTitle() == (string)$room) {
                    $configRoom = new Room();
                    $configRoom->setRoomType($roomType)->setRoomId((string)$room->attributes()->id);
                    $config->addRoom($configRoom);
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    private function getHeaders(OktogoConfig $config)
    {
        $auth = 'Authorization: Basic ' . base64_encode($config->getUsername() . ':' . $config->getPassword());
        if(static::TEST) {
            $auth = 'Authorization: Basic ' . base64_encode('Oktogo:Oktogo');
        }
        $xAuth = 'X-Oktogo-Authorization: Basic ' . base64_encode($config->getUsername() . ':' . $config->getPassword());

        return array_merge($this->headers, [$auth, $xAuth]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {
        return true;
    }

    public function createPackages()
    {
        return new Response();
    }

    /**
     * {@inheritDoc}
     */
    public function getRooms(ChannelManagerConfigInterface $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getTariffs(ChannelManagerConfigInterface $config)
    {
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config) {}

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config) {}
}
