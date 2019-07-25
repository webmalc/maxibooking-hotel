<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\ICalType;

use MBH\Bundle\ChannelManagerBundle\Document\Room;

abstract class AbstractICalTypeChannelManagerRoom extends Room
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
     * @return self
     */
    public function setSyncUrl(string $syncUrl): self
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}
