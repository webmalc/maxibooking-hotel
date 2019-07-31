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
    public const PERIOD_LENGTH = '1 year';
    protected const CLOSED_PERIOD_SUMMARY = 'TODOTODOTODOTODOTODO';
    protected const CLOSED_PERIOD_SUMMARY_ELEMENT = 'TODOTODOTODOTODOTODO';

    /**
     * @return string
     */
    protected function getCheckClosedPeriodElement(): string
    {
        return self::CLOSED_PERIOD_SUMMARY_ELEMENT;
    }

    /**
     * @return string
     */
    protected function getPeriodLength(): string
    {
        return self::PERIOD_LENGTH;
    }

    /**
     * @return string
     */
    protected function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    protected function getClosedPeriodSummary(): string
    {
        return self::CLOSED_PERIOD_SUMMARY;
    }

    /**
     * @return AbstractICalTypeOrderInfo
     */
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
