<?php

namespace MBH\Bundle\VegaBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class VegaFMS
 * @package MBH\Bundle\VegaBundle\Document
 *
 * @ODM\Document(collection="vega_fms")
 * @Gedmo\Loggable
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaFMS extends Base
{
    /**
     * @var string
     * @ODM\String
     * @Gedmo\Versioned
     */
    protected $code;

    /**
     * @var string
     * @ODM\String
     * @Gedmo\Versioned
     */
    protected $name;

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
}