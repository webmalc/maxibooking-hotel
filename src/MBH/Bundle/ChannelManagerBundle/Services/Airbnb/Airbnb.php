<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeChannelManagerService;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypeOrderInfo;

class Airbnb extends AbstractICalTypeChannelManagerService
{
    public const NAME = 'airbnb';
    public const DOMAIN_NAME = self::NAME;
    public const SYNC_URL_BEGIN = 'https://www.' . self::DOMAIN_NAME . '.';
    public const CONFIG = 'AirbnbConfig';
    public const PERIOD_LENGTH = '1 year';
    protected const CLOSED_PERIOD_SUMMARY = 'Not available';

    /** @return string */
    protected function getPeriodLength(): string
    {
        return self::PERIOD_LENGTH;
    }

    /** @return string */
    protected function getName(): string
    {
        return self::NAME;
    }

    /** @return string */
    protected function getClosedPeriodSummary(): string
    {
        return self::CLOSED_PERIOD_SUMMARY;
    }

    /** @return AbstractICalTypeOrderInfo */
    protected function getOrderInfoService(): AbstractICalTypeOrderInfo
    {
        return $this->container->get('mbh.airbnb_order_info');
    }

    /**
     * @param RoomType $roomType
     * @return string
     * @throws \Exception
     */
    public function generateRoomCalendar(RoomType $roomType): string
    {
        return $this->generateCalendar($roomType, $roomType->getHotel()->getAirbnbConfig());
    }
}
