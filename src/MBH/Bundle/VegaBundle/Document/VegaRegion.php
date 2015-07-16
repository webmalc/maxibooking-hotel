<?php

namespace MBH\Bundle\VegaBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class VegaRegion
 * @ODM\Document(collection="vega_region")
 * @Gedmo\Loggable
 * @package MBH\Bundle\VegaBundle\Document
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaRegion extends Base
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
}