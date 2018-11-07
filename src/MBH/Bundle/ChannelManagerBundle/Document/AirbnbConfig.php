<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\ICalService\Airbnb;

/**
 * @ODM\Document(collection="AirbnbConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class AirbnbConfig extends ICalServiceConfig implements ChannelManagerConfigInterface
{
    public function getName()
    {
        return Airbnb::NAME;
    }
}