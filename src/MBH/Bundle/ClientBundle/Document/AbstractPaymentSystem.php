<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

class AbstractPaymentSystem
{
    /**
     * @var boolean
     * @ODM\Boolean(name="isEnabled")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isEnabled = false;

    /**
     * Get isEnabled
     *
     * @return boolean $isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param boolean $isEnabled
     * @return self
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}