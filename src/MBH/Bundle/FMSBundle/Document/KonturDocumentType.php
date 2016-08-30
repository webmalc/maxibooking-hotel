<?php

namespace MBH\Bundle\FMSBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class DocumentType
 * @package MBH\Bundle\FMSBundle\Document
 * @Gedmo\Loggable
 * @ODM\Document(collection="kontur_document_type")
 */
class KonturDocumentType extends Base
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @return string
     */
    public function getActualityStatus(): string
    {
        return $this->actualityStatus;
    }

    /**
     * @param string $actualityStatus
     */
    public function setActualityStatus(string $actualityStatus)
    {
        $this->actualityStatus = $actualityStatus;
    }

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Gedmo\Versioned
     */
    protected $actualityStatus;
}