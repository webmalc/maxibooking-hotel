<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;

class ChannelManagerHelper
{

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации типов комнат сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypesSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {
        $roomTypes = [];
        foreach ($config->getRooms() as $room) {
            /** @var Room $room */
            $roomType = $room->getRoomType();
            if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $roomTypes[$room->getRoomId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            } else {
                $roomTypes[$roomType->getId()] = [
                    'syncId' => $room->getRoomId(),
                    'doc' => $roomType
                ];
            }
        }

        return $roomTypes;
    }

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации тарифов сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getTariffsSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {
        $tariffs = [];
        foreach ($config->getTariffs() as $configTariff) {
            /** @var \MBH\Bundle\ChannelManagerBundle\Document\Tariff $configTariff */
            $tariff = $configTariff->getTariff();

            if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                continue;
            }

            if ($byService) {
                $tariffs[$configTariff->getTariffId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            } else {
                $tariffs[$tariff->getId()] = [
                    'syncId' => $configTariff->getTariffId(),
                    'doc' => $tariff
                ];
            }
        }

        return $tariffs;
    }
}
