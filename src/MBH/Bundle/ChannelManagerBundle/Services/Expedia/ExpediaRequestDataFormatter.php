<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Expedia;

use MBH\Bundle\ChannelManagerBundle\Lib\AbstractRequestDataFormatter;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;

class ExpediaRequestDataFormatter extends AbstractRequestDataFormatter
{
    protected function formatPriceData(
        PriceCache $priceCache,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        \DateTime $day
    ) {
        // TODO: Implement formatPriceData() method.
    }

    protected function formatRestrictionData(
        Restriction $restriction,
        $serviceRoomTypeId,
        $serviceTariffId,
        &$resultArray,
        $isPriceSet,
        \DateTime $day
    ) {
        // TODO: Implement formatRestrictionData() method.
    }

    protected function formatRoomData(RoomCache $roomCache, $serviceRoomTypeId, &$resultArray, \DateTime $day)
    {
        // TODO: Implement formatRoomData() method.
    }
}