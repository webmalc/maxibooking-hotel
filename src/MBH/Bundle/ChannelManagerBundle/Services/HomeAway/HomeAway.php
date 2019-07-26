<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;
use MBH\Bundle\HotelBundle\Document\RoomType;


class HomeAway extends AbstractICalTypeChannelManagerService
{
    public const CHANNEL_MANAGER_TYPE = 'homeAway';
    public const NAME = 'homeaway';
    public const CONFIG = 'HomeAwayConfig';
    protected const PERIOD_LENGTH = '1 year';
    protected const CLOSED_PERIOD_SUMMARY = 'TODOTODOTODOTODOTODO';

    protected function getPeriodLength(): string
    {
        return self::PERIOD_LENGTH;
    }

    protected function getName(): string
    {
        return self::NAME;
    }

    protected function getClosedPeriodSummary(): string
    {
        return self::CLOSED_PERIOD_SUMMARY;
    }

    protected function getOrderInfoService(): AbstractICalTypeOrderInfo
    {
        return $this->container->get('mbh.homeaway_order_info');
    }

    /**
     * @param RoomType $roomType
     * @return string
     * @throws \Exception
     */
    public function generateRoomCalendar(RoomType $roomType): string
    {
        return $this->generateCalendar($roomType, $roomType->getHotel()->getHomeAwayConfig());
    }
}
