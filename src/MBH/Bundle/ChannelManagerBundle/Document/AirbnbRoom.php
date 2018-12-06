<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class AirbnbRoom extends Room
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $syncUrl;

    /**
     * @return string
     */
    public function getSyncUrl(): ?string
    {
        return $this->syncUrl;
    }

    /**
     * @param string $syncUrl
     * @return AirbnbRoom
     */
    public function setSyncUrl(string $syncUrl): AirbnbRoom
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}