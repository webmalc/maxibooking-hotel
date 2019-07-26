<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;

class HomeAwayPackageInfo extends AbstractICalTypePackageInfo
{

    public function getBeginDate()
    {
        throw new \Exception('implement me');
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTSTART'])->modify('midnight');
    }

    public function getEndDate()
    {
        throw new \Exception('implement me');
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTEND'])->modify('midnight');
    }

    public function getTourists(): array
    {
        throw new \Exception('implement me');
    }

    public function getChannelManagerId()
    {
        throw new \Exception('implement me');
    }

    public function getNote(): string
    {
        throw new \Exception('implement me');
    }

}
