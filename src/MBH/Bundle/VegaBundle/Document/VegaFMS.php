<?php

namespace MBH\Bundle\VegaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;

/**
 * Class VegaFMS
 * @package MBH\Bundle\VegaBundle\Document
 *
 * @ODM\Document(collection="vega_fms")
 * @Gedmo\Loggable

 */
class VegaFMS extends Base
{
    /**
     * @var string
     * @ODM\Field(type="string") 
     * @Gedmo\Versioned
     */
    protected $code;

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

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