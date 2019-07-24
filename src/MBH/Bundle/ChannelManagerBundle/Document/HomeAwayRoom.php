<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class HomeAwayRoom extends Room
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
     * @return HomeAwayRoom
     */
    public function setSyncUrl(string $syncUrl): HomeAwayRoom
    {
        $this->syncUrl = $syncUrl;

        return $this;
    }
}
