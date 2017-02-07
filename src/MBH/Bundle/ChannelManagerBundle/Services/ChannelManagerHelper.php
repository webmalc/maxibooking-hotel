<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;

class ChannelManagerHelper
{
    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isTariffsSyncDataInit = false;
    private $tariffsSyncData;

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации типов комнат сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypesSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {

        if (!$this->isRoomTypesInit) {

            foreach ($config->getRooms() as $room) {
                /** @var Room $room */
                $roomType = $room->getRoomType();
                if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->roomTypes[$room->getRoomId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                } else {
                    $this->roomTypes[$roomType->getId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                }
            }

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }

    /**
     * Метод формирования периодов(цен, ограничений, доступности комнат) из массива данных о ценах, ограничениях, доступностях комнат
     *
     * @param array $entities
     * @param array $comparePropertyMethods Массив имен методов, используемых для сравнения переданных сущностей
     * @param bool $isSorted Отсортирован ли переданный массив данных по дате
     * @return array
     */
    public function getPeriodsFromDayEntities(array $entities, array $comparePropertyMethods, $isSorted = false)
    {
        $isSorted ?: $entities = $this->sortEntitiesByDate($entities);

        $periods = [];
        $currentPeriod = null;
        $lastIteratedEntity = null;
        foreach ($entities as $entity) {
            if ($currentPeriod) {
                if ($currentPeriod && $this->isEntityEquals($entity, $lastIteratedEntity, $comparePropertyMethods)) {
                    $currentPeriod->getEndDate()->modify('+1 day');
                } else {
                    $periods[] = $currentPeriod;
                    $currentPeriod = new \DatePeriod($entity->getDate(), new \DateInterval('P1D'), $entity->getDate());
                }
            } else {
                $currentPeriod = new \DatePeriod($entity->getDate(), new \DateInterval('P1D'), $entity->getDate());
            }
        }

        return $periods;
    }

    /**
     * Сортируем сущности цен, ограничений и доступности комнат по дате
     *
     * @param array $entities
     * @return array
     */
    public function sortEntitiesByDate(array $entities) : array
    {
        usort($entities, function ($a, $b) {
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        });

        return $entities;
    }

    private function isEntityEquals($firstEntity, $secondEntity, $comparePropertyMethods)
    {
        $isEqual = true;
        foreach ($comparePropertyMethods as $comparePropertyMethod) {
            if ($firstEntity->{$comparePropertyMethod}() != $secondEntity->{$comparePropertyMethod}()) {
                $isEqual = false;
            }
        }

        return $isEqual;
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
        if (!$this->isTariffsSyncDataInit) {

            foreach ($config->getTariffs() as $configTariff) {
                /** @var \MBH\Bundle\ChannelManagerBundle\Document\Tariff $configTariff */
                $tariff = $configTariff->getTariff();

                if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->tariffsSyncData[$configTariff->getTariffId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                } else {
                    $this->tariffsSyncData[$tariff->getId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                }
            }

            $this->isTariffsSyncDataInit = true;
        }

        return $this->tariffsSyncData;
    }

}