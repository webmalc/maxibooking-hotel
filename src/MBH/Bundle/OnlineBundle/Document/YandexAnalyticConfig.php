<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @Gedmo\Loggable
 * @ODM\EmbeddedDocument
 */
class YandexAnalyticConfig
{
    /**
     * @var string
     * @ODM\Field(type="string")
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
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
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
}