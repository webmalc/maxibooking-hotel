<?php

namespace MBH\Bundle\VegaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;

/**
 * Class VegaRegion
 * @ODM\Document(collection="vega_region")
 * @Gedmo\Loggable
 * @package MBH\Bundle\VegaBundle\Document

 */
class VegaRegion extends Base
{
    /**
     * @var string
     * @ODM\Field(type="string") 
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @var string
     * @ODM\Field(type="string") 
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
}