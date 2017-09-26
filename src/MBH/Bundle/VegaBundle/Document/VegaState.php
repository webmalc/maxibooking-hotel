<?php

namespace MBH\Bundle\VegaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * Class VegaFMS
 * @package MBH\Bundle\VegaBundle\Document
 *
 * @MongoDBUnique(fields="name", message="vega_state.error.unique_name")
 * @ODM\Document(collection="vega_states")
 * @Gedmo\Loggable
 */
class VegaState extends Base
{
    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     * @Assert\Regex("/[a-zа-я]/i")
     * @Gedmo\Versioned
     */
    protected $originalName;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Regex("/[a-zа-я]/i")
     * @Assert\NotNull()
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return VegaState
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param $originalName
     * @return VegaState
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @deprecated
     * @return string
     */
    public function getTitle()
    {
        return $this->getName();
    }

    public function __toString()
    {
        return is_string($this->name) ? $this->name : '';
    }
}