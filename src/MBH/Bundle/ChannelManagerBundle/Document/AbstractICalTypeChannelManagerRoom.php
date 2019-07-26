<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
abstract class AbstractICalTypeChannelManagerRoom extends Room
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $syncUrl;

    /**
     * @return string
     */
    public function getSyncUrl(): ?string
    {
        return $this->syncUrl;
    }

    /**
     * @param string $syncUrl
     * @return self
     */
    public function setSyncUrl(string $syncUrl): self
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}
