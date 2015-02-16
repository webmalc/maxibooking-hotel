<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\MappedSuperclass
 * @Gedmo\Loggable
 */
class Base
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isEnabled")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isEnabled = true;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return self
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

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
     * @return string
     */
    public function __toString()
    {
        if (method_exists($this, 'getName')) {
            return $this->getName();
        }

        return (string) $this->getId();
    }

    public function __clone()
    {
        $this->id = null;
    }
    
}
