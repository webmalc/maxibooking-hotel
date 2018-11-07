<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\ICalService;

class Homeaway extends ICalService
{
    const NAME = 'homeaway';
    const HUMAN_NAME = 'Homeaway';
    //TODO: Заменить
    const SYNC_URL_BEGIN = 'https://www.airbnb.';
    const CONFIG = 'HomeawayConfig';

    protected function getName()
    {
        return self::NAME;
    }

    protected function getHumanName()
    {
        return self::HUMAN_NAME;
    }
}