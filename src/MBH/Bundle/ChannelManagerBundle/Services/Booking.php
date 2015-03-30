<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService as Base;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;

/**
 *  ChannelManager service
 */
class Booking extends Base
{

    /**
     * Config class
     */
    const CONFIG = 'BookingConfig';

    /**
     * Base API URL
     */
    const BASE_URL = 'https://supply-xml.booking.com/hotels/xml/';
    
    public $servicesConfig = [
        1 => 'Breakfast',
        2 => 'Continental breakfast',
        3 => 'American breakfast',
        4 => 'Buffet breakfast',
        5 => 'Full english breakfast',
        6 => 'Lunch',
        7 => 'Dinner',
        8 => 'Half board',
        9 => 'Full board',
        11 => 'Breakfast for Children',
        12 => 'Continental breakfast for Children',
        13 => 'American breakfast for Children',
        14 => 'Buffet breakfast for Children',
        15 => 'Full english breakfast for Children',
        16 => 'Lunch for Children',
        17 => 'Dinner for Children',
        18 => 'Half board for Children',
        19 => 'Full board for Children',
        20 => 'WiFi',
        21 => 'Internet',
        22 => 'Parking space',
        23 => 'Extrabed',
        24 => 'Babycot'
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
    
    /**
     * {@inheritDoc}
     */
    public function closeAll()
    {
        $result = false;
        
        foreach ($this->getConfig() as $config) {
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:close.xml.twig', [
                'config' => $config, 'rooms' => $this->getRoomTypes($config), 'rates' => $this->getTariffs($config)]
            );
            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);
            $result = $this->checkResponse($sendResult);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {

        $result = false;

        // iterate hotels
        foreach ($this->getConfig() as $config) {
            $tariffs = $this->getTariffs($config);
            $roomTypes = $this->getRoomTypes($config);
            $caches = $this->dm->getRepository('MBHPackageBundle:RoomCache')
                    ->fetch($begin, $end, $roomType, array_keys($roomTypes), array_keys($tariffs));
            
            if(!$caches->count()) {
                continue;
            }
            
            //group cache
            $data = [];
            foreach ($caches as $cache) {
                $roomType = $cache->getRoomType();
                $tariff = $cache->getTariff();
                $roomTypeSyncId = $roomTypes[$roomType->getId()]['syncId'];
                $tariffSyncId = $tariffs[$tariff->getId()]['syncId'];
                $formattedDate = $cache->getDate()->format('Y-m-d');
                $price = $cache->getPrice()->getPrice();

                if (!is_numeric($price)) {
                    continue;
                }
                
                $data[$roomTypeSyncId][$formattedDate][$tariffSyncId] = 
                [
                    'roomstosell' => $cache->getRooms(),
                    'closed' => empty($cache->getRooms()) ? 1 : 0
                ];
                
                if ($tariff->getMinPackageDuration()) {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId]['minimumstay'] = $tariff->getMinPackageDuration();
                }
                if ($tariff->getMaxPackageDuration()) {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId]['maximumstay'] = $tariff->getMaxPackageDuration();
                }
                if ($roomType->getCalculationType() == 'perRoom') {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId]['price'] = $price;
                } else {
                    $data[$roomTypeSyncId][$formattedDate][$tariffSyncId]['price1'] = $price;
                }
            }
            
            $request = $this->templating->render('MBHChannelManagerBundle:Booking:update.xml.twig', ['config' => $config, 'data' => $data]);

            //header("Content-type: text/xml; charset=utf-8");
            //echo($request); exit();

            $sendResult = $this->send(static::BASE_URL . 'availability', $request, null, true);
            $result = $this->checkResponse($sendResult);
        }
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function checkResponse($response, array $params = null)
    {
        if (!$response) {
            return false;
        }
        $xml = simplexml_load_string($response);

        return count($xml->xpath('/ok')) ? true : false;;
    }

    /**
     * {@inheritDoc}
     */
    public function createPackages()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function sync()
    {
        $configs = $this->getConfig();

        if (empty($configs)) {
            throw new \Exception('Config not found');
        }
        foreach ($configs as $config) {

            $request = $this->templating->render('MBHChannelManagerBundle:Booking:get.xml.twig', ['config' => $config]);
            $hotel = $config->getHotel();

            // rooms
            $response = $this->sendXml(static::BASE_URL . 'rooms', $request);
            $config->removeAllRooms();
            foreach ($response->xpath('room') as $room) {
                foreach($hotel->getRoomTypes() as $roomType) {
                    if ($roomType->getFullTitle() == (string)$room ) {
                        $configRoom = new Room();
                        $configRoom->setRoomType($roomType)->setRoomId((string)$room['id']);
                        $config->addRoom($configRoom);
                        $this->dm->persist($config);
                    }
                }
            }
            $this->dm->flush();

            //tariffs
            $response = $this->sendXml(static::BASE_URL . 'rates', $request);
            $config->removeAllTariffs();
            
            foreach ($response->xpath('rate') as $rate) {
                
                foreach($hotel->getTariffs() as $tariff) {
                    
                    if ($tariff->getFullTitle() == (string)$rate ) {
                        $configTariff = new Tariff();
                        $configTariff->setTariff($tariff)->setTariffId((string)$rate['id']);
                        $config->addTariff($configTariff);
                        $this->dm->persist($config);
                    }
                }
            }
            
            //services
            $config->removeAllServices();
            foreach ($this->servicesConfig as $serviceKey => $serviceName) {
                $serviceDoc = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy([
                    'code' => $serviceName
                ]);
                
                if(empty($serviceDoc) || $serviceDoc->getCategory()->getHotel()->getId() != $config->getHotel()->getId()) {
                    continue;
                }
                
                $service = new \MBH\Bundle\ChannelManagerBundle\Document\Service();
                $service->setServiceId($serviceKey)->setService($serviceDoc);
                $config->addService($service);
                $this->dm->persist($config);
            }
            
            $this->dm->flush();

        }
        return $config;
    }

}
