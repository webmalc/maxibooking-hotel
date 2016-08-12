<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 */
class Service
{
    /**
     * @var \MBH\Bundle\PriceBundle\Document\Service
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PriceBundle\Document\Service")
     * @Assert\NotNull()
     */
    protected $service;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $serviceId;

    /**
     * Set service
     *
     * @param \MBH\Bundle\PriceBundle\Document\Service $service
     * @return self
     */
    public function setService(\MBH\Bundle\PriceBundle\Document\Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Get service
     *
     * @return MBH\Bundle\PriceBundle\Document\Service $service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set serviceId
     *
     * @param string $serviceId
     * @return self
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * Get serviceId
     *
     * @return string $serviceId
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
