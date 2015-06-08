<?php

namespace MBH\Bundle\VegaBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class VegaRegion
 * @ODM\Document(collection="vega_region")
 * @package MBH\Bundle\VegaBundle\Document
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaRegion extends Base
{
    /**
     * @var string
     * @ODM\String
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}