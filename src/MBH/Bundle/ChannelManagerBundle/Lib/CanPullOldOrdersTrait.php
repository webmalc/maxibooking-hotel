<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

trait CanPullOldOrdersTrait
{
    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    protected $isAllPackagesPulled = false;

    /**
     * @return bool
     */
    public function isAllPackagesPulled(): ?bool
    {
        return $this->isAllPackagesPulled;
    }

    /**
     * @param bool $isAllPackagesPulled
     * @return static
     */
    public function setIsAllPackagesPulled(bool $isAllPackagesPulled)
    {
        $this->isAllPackagesPulled = $isAllPackagesPulled;

        return $this;
    }
}
