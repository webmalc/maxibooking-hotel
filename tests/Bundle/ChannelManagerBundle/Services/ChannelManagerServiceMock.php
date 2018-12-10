<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ChannelManagerServiceMock
{
    const FIRST_ROOM_ID = 'ID1';
    const FIRST_ROOM_NAME = 'Room name';
    const SECOND_ROOM_ID = 'ID2';
    const SECOND_ROOM_NAME = 'Room name 2';
    const FIRST_TARIFF_ID = 'ID1';
    const FIRST_TARIFF_NAME = 'Tariff 1';

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {

        return [
            self::FIRST_ROOM_ID => self::FIRST_ROOM_NAME,
            self::SECOND_ROOM_ID => self::SECOND_ROOM_NAME
        ];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        return [
            'ID1' => [
                'title' => self::FIRST_TARIFF_NAME,
                'readonly' => false,
                'is_child_rate' => false,
                'rooms' => [
                    self::FIRST_ROOM_ID => self::FIRST_ROOM_NAME,
                    self::SECOND_ROOM_ID => self::SECOND_ROOM_NAME
                ]
            ]
        ];
    }

    public function sendTestRequestAndGetErrorMessage(ChannelManagerConfigInterface $config)
    {
        return null;
    }

    public function safeConfigDataAndGetErrorMessage()
    {
        return '';
    }

    public function associateUser($username, $password)
    {
        return 'some-token';
    }

    public function roomList(MyallocatorConfig $config, $grouped = false)
    {
        if ($grouped) {
            return $this->pullRooms($config);
        }

        return [
            ['Disabled' => false, 'RoomId' => self::FIRST_ROOM_ID],
            ['Disabled' => false, 'RoomId' => self::SECOND_ROOM_ID],
        ];
    }

    public function propertyList(MyallocatorConfig $config)
    {
        return [
            ['id' => self::FIRST_ROOM_ID, 'name' => 'First']
        ];
    }

    public function syncServices(ChannelManagerConfigInterface $config)
    {

    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getNotifications(ChannelManagerConfigInterface $config): array
    {
        return [];
    }

    public function isBookingAccountConfirmed(BookingConfig $config)
    {
        return true;
    }
}