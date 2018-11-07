<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\ChannelManagerBundle\Services\ICalService\Homeaway;

/**
 * @ODM\Document(collection="HomeawayConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class HomeawayConfig extends ICalServiceConfig
{
    public function getName()
    {
        return Homeaway::NAME;
    }
}