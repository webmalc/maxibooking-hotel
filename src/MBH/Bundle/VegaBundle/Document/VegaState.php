<?php

namespace MBH\Bundle\VegaBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class VegaFMS
 * @package MBH\Bundle\VegaBundle\Document
 *
 * @ODM\Document(collection="vega_states")
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaState extends Base
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

    public function __toString()
    {
        return is_string($this->name) ? $this->name : '';
    }
}