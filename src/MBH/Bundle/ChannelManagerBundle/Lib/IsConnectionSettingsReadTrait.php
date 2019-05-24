<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

trait IsConnectionSettingsReadTrait
{
    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    protected $isConnectionSettingsRead = false;

    /**
     * @return bool
     */
    public function isConnectionSettingsRead(): ?bool
    {
        return $this->isConnectionSettingsRead;
    }

    /**
     * @param bool $isConnectionSettingsRead
     * @return IsConnectionSettingsReadTrait
     */
    public function setIsConnectionSettingsRead(bool $isConnectionSettingsRead): self
    {
        $this->isConnectionSettingsRead = $isConnectionSettingsRead;

        return $this;
    }
}
