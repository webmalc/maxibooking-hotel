<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;

interface ChannelManagerConfigInterface
{
    public function getIsEnabled();

    /**
     * @return bool
     */
    public function isReadyToSync(): bool;

    /**
     * @return Hotel
     */
    public function getHotel();

    public function setHotel(Hotel $hotel);

    public function getHotelId();

    public function setHotelId($hotelId);

    /**
     * @return string
     */
    public function getName();

    public function removeAllRooms();

    public function getRooms();

    public function addRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room);

    public function removeRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room);

    public function removeAllTariffs();

    public function getTariffs();

    public function addTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff);

    public function removeTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff);
}