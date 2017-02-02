<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;

class HomeAwayDataFormatter extends AbstractRequestDataFormatter
{

    /**
     * Форматирование данных, отправляемых в запросе обновления цен сервиса
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatPriceRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    ) {
        // TODO: Implement formatPriceRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления квот на комнаты
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRoomRequestData($begin, $end, $roomTypes, ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatRoomRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе обновления ограничений
     * @param $begin
     * @param $end
     * @param $roomTypes
     * @param $serviceTariffs
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatRestrictionRequestData(
        $begin,
        $end,
        $roomTypes,
        $serviceTariffs,
        ChannelManagerConfigInterface $config
    ) {
        // TODO: Implement formatRestrictionRequestData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе закрытия продаж
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatCloseForConfigData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatCloseForConfigData() method.
    }

    /**
     * Форматирование данных, отправляемых в запросе получения броней
     * @param ChannelManagerConfigInterface $config
     * @return mixed
     */
    public function formatGetBookingsData(ChannelManagerConfigInterface $config)
    {
        // TODO: Implement formatGetBookingsData() method.
    }
}