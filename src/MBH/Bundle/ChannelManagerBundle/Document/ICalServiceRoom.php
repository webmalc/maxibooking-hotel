<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class ICalServiceRoom extends Room
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
     * @return ICalServiceRoom
     */
    public function setSyncUrl(string $syncUrl): ICalServiceRoom
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}