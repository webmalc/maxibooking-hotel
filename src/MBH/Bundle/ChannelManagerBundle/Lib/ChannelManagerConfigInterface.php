<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\HotelBundle\Document\Hotel;

interface ChannelManagerConfigInterface
{
    public function getIsEnabled();

    public function isMainSettingsFilled();
    public function isConfirmedWithDataWarnings(): ?bool;
    public function setIsConfirmedWithDataWarnings(bool $isConfirmedWithDataWarnings);

    /**
     * @param bool $checkOldPackages
     * @return bool
     */
    public function isReadyToSync($checkOldPackages = true): bool;

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
     * @return ArrayCollection|array|Room[]
     */
    public function getRooms();

    public function addRoom(Room $room);

    public function removeRoom(Room $room);

    public function removeAllTariffs();

    /**
     * @return ArrayCollection|array|Tariff[]
     */
    public function getTariffs();

    public function addTariff(Tariff $tariff);

    public function removeTariff(Tariff $tariff);
}