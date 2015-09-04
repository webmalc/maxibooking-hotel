<?php

namespace MBH\Bundle\VegaBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class VegaFMS
 * @package MBH\Bundle\VegaBundle\Document
 *
 * @ODM\Document(collection="vega_states")
 * @Gedmo\Loggable
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class VegaState extends Base
{
    /**
     * @var string
     * @ODM\String
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @var string
     * @ODM\String
     * @Gedmo\Versioned
     */
    protected $originalName;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
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