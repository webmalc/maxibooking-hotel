<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class ChannelManagerServiceMock
{
    const FIRST_ROOM_ID = 'ID1';
    const SECOND_ROOM_ID = 'ID2';
    const FIRST_TARIFF_ID = 'ID1';

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config) {

        return [
            self::FIRST_ROOM_ID => 'Room name',
            self::SECOND_ROOM_ID => 'Room name 2'
        ];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config) {
        return [
            'ID1' => [
                'title' => 'Тариф 1',
                'readonly' => false,
                'is_child_rate' => false,
                'rooms' => [
                    self::FIRST_ROOM_ID => 'Room name',
                    self::SECOND_ROOM_ID => 'Room name 2'
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

    public function associateUser($username, $password) {
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
}