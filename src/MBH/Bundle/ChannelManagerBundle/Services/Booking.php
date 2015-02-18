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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @inherit
     */
    public function update (\DateTime $begin = null, \DateTime $end = null, RoomType $roomType = null)
    {

    }

    /**
     * @inherit
     */
    public function createPackages()
    {

    }

    /**
     * @inherit
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
            $this->dm->flush();

        }
        return $config;
    }

}
