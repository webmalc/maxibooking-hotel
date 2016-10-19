<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\OktogoConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

/**
 *  ChannelManager service
 */
class Oktogo extends Base
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
        $result = true;
        $begin = $this->getDefaultBegin($begin);
        $end = $this->getDefaultEnd($begin, $end);

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $roomTypes = $this->getRoomTypes($config);
            $roomCaches = $this->dm->getRepository('MBHPriceBundle:RoomCache')->fetch(
                $begin,
                $end,
                $config->getHotel(),
                $roomType ? [$roomType->getId()] : [],
                null,
                true
            );


//
//            dump($roomTypes);
//            dump($roomCaches);
////            exit();

            foreach($roomTypes as $roomTypeId => $roomTypeInfo){

                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {

                    if(isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])){

                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
//                        dump($info);
//                        dump($roomTypeInfo);

                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                            'roomstosell' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0,
                            'closed' => $info->getIsClosed() > 0 ? 1 : 0
                        ];

                    } else {
                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
                            'roomstosell' => 0
                        ];
                    }
                }

            }
            dump($data);
//            exit();
//            foreach ($roomTypes as $roomTypeId => $roomTypeInfo) {
//
//                foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end) as $day) {
//
//                    if (isset($roomCaches[$roomTypeId][0][$day->format('d.m.Y')])) {
//
//                        $info = $roomCaches[$roomTypeId][0][$day->format('d.m.Y')];
//                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
//                            'roomstosell' => $info->getLeftRooms() > 0 ? $info->getLeftRooms() : 0
//                        ];
//
//                    } else {
//
//                        $data[$roomTypeInfo['syncId']][$day->format('Y-m-d')] = [
//                            'roomstosell' => 0
//                        ];
//
//                    }
//                }
//            }

            if (!isset($data)) {
                continue;
            }

            $request = $this->templating->render(
                'MBHChannelManagerBundle:Oktogo:updateRooms.xml.twig',
                [
                    'config' => $config,
                    'data' => $data,
                ]
            );
            dump($request);

            $sendResult = $this->send(static::BASE_URL.'availability', $request, $this->base64Encodes($config), true);

            dump($sendResult);
            exit();
            if ($result) {
                $result = $this->checkResponse($sendResult);
            }

            $this->log($sendResult);
        }

        return $result;
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
    public function pullRooms(ChannelManagerConfigInterface $config) {

        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Oktogo:getRoomsTariffs.xml.twig',
            ['config' => $config]
        );

//        $response = $this->send(static::BASE_URL.'rooms', $request,$this->base64Encodes($config));

        $response = $this->sendXml(static::BASE_URL.'rooms',$request,$this->base64Encodes($config));

        dump($response);

        //$this->log($response->asXML());
//        dump($response->room[2]->attributes());
//        exit();
        foreach ($response->room as $rooms ) {


            $attr = $rooms->attributes();
            $result[(string)$attr['id']] = $attr['room_name'];
        }
//        dump($result);
//        exit();
        return $result;
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config) {
        $result = [];
        $request = $this->templating->render(
            'MBHChannelManagerBundle:Oktogo:getRoomsTariffs.xml.twig',
            ['config' => $config]
        );

        $response = $this->sendXml(static::BASE_URL.'rateplans',$request,$this->base64Encodes($config));

        dump($response);



            foreach ($response->rateplan as $rate) {
                    dump((string)$rate );

                    $attr = $rate->attributes();
//                dump($rate[0]);
//                if (isset($result[(string)$rate['id']]['rooms'])) {
//                    $rooms = $result[(string)$rate['id']]['rooms'];
//                } else {
//                    $rooms = [];
//                }
//
//                $rooms[(string)$room['id']] = (string)$room['id'];


                $result[(string)$attr['id']] = [
                    'title' => (string)$rate,
                    'readonly' => empty((int)$rate['readonly']) ? false : true,
                    'is_child_rate' => empty((int)$rate['is_child_rate']) ? false : true,
                    'rooms' => $attr['room_id']
                ];
            }


        return $result;
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
     * @return \MBH\Bundle\ChannelManagerBundle\Lib\Response
     */
    public function pushResponse(Request $request)
    {
        // TODO: Implement pushResponse() method.
    }

    /**
     * @return $this
     */
    public function removeAllRooms()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    private function base64Encodes($config){
        if(self::TEST){
            $this->headers[] =  'Authorization: Basic '.base64_encode('Oktogo:Oktogo');
            $this->headers[] =  'X-Oktogo-Authorization: Basic '.base64_encode($config->getLogin().':'.$config->getPassword());
            return $this->headers;
        }
    }

}
