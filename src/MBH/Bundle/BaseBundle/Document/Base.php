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
     * Get isEnabled
     *
     * @return boolean $isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
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
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (method_exists($this, 'getTitle') && !empty($this->getTitle())) {
            return (string) $this->getTitle();
        }
        if (method_exists($this, 'getFullTitle') && !empty($this->getFullTitle())) {
            return (string) $this->getFullTitle();
        }

        return (string) $this->getId();
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    public function __clone()
    {
        $this->id = null;
        $this->isEnabled = false;
        $now = new \DateTime();
        $copyName = '_copy_' . $now->format('d.m.Y_H:i');

        if (method_exists($this, 'setFullTitle') && !empty($this->getFullTitle())) {
            $this->setFullTitle($this->getFullTitle() . $copyName);
        }
        if (method_exists($this, 'setTitle') && !empty($this->getTitle())) {
            $this->setTitle($this->getTitle() . $copyName);
        }
        if (property_exists($this, 'createdAt')) {
            $this->createdAt = null;
        }
        if (property_exists($this, 'updatedAt')) {
            $this->updatedAt = null;;
        }
        if (method_exists($this, 'createdBy')) {
            $this->createdBy = null;
        }
        if (method_exists($this, 'setUpdatedBy')) {
            $this->updatedBy = null;
        }
    }
    
}
