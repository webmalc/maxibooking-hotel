<?php
namespace MBH\Bundle\ChannelManagerBundle\Lib;


trait ConfigTrait
{
    public function getRoomsAsArray()
    {
        $result = [];

        foreach ($this->getRooms() as $room) {
            $result[$room->getRoomId()] = $room->getRoomType();
        }

        return $result;
    }

    public function getTariffsAsArray()
    {
        $result = [];

        foreach ($this->getTariffs() as $tariff) {
            $result[$tariff->getTariffId()] = $tariff->getTariff();
        }

        return $result;
    }
}