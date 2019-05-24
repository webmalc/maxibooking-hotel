<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @Gedmo\Loggable
 * @ODM\EmbeddedDocument
 */
class GoogleAnalyticConfig
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $id;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isEnabled = false;

    /**
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GoogleAnalyticConfig
     */
    public function setId(string $id): GoogleAnalyticConfig
    {
        $this->id = $id;

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
