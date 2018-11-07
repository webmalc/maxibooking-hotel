<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\ICalService;

class Airbnb extends ICalService
{
    const NAME = 'airbnb';
    const HUMAN_NAME = 'Airbnb';
    const SYNC_URL_BEGIN = 'https://www.airbnb.';
    const CONFIG = 'AirbnbConfig';

    protected function getName()
    {
        return self::NAME;
    }

    protected function getHumanName()
    {
        return self::HUMAN_NAME;
    }
}