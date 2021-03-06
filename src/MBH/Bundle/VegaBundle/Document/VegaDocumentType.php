<?php

namespace MBH\Bundle\VegaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;

/**
 * Class VegaDocumentType
 * @ODM\Document(collection="vega_document_type", repositoryClass="\MBH\Bundle\VegaBundle\Document\VegaDocumentTypeRepository")
 * @Gedmo\Loggable

 */
class VegaDocumentType extends Base
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
     * @var string
     * @ODM\Field(type="string") 
     * @ODM\UniqueIndex
     * @Gedmo\Versioned
     */
    protected $code;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;//is_string($this->getOriginalName()) ? FriendlyFormatter::convertDocumentType($this->getOriginalName()) : null;
    }

    /**
     * @param string $name
     * @return self
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
     * @param string $originalName
     * @return self
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}