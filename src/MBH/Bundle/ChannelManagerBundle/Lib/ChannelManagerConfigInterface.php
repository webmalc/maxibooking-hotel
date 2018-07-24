<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\Hotel;

interface ChannelManagerConfigInterface
{
    public function getIsEnabled();

    public function isMainSettingsFilled();

    /**
     * @param bool $checkOldPackages
     * @return bool
     */
    public function isReadyToSync($checkOldPackages = false): bool;
    public function isReadinessConfirmed(): bool;

    /**
     * @param bool $isReadinessConfirmed
     * @return ChannelManagerConfigInterface
     */
    public function setReadinessConfirmed(bool $isReadinessConfirmed);

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

    /**
     * @return ArrayCollection|array
     */
    public function getRooms();

    public function addRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room);

    public function removeRoom(\MBH\Bundle\ChannelManagerBundle\Document\Room $room);

    public function removeAllTariffs();

    /**
     * @return ArrayCollection|array
     */
    public function getTariffs();

    public function addTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff);

    public function removeTariff(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff);
}