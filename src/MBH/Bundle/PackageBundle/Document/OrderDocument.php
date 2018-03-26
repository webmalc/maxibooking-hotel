<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\ProtectedFile;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 * @ODM\HasLifecycleCallbacks
 *

 */
class OrderDocument
{
    use TimestampableDocument;
    use BlameableDocument;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="validator.document.OrderDocument.type")
     */
    protected $type;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $scanType;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $originalName;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $comment;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Tourist")
     */
    protected $tourist;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization")
     */
    protected $organization;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument")
     */
    protected $cashDocument;


    /**
     * @var ProtectedFile
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\BaseBundle\Document\ProtectedFile", cascade={"persist"})
     */
    protected $file;


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getScanType()
    {
        return $this->scanType;
    }

    /**
     * @param string $scanType
     */
    public function setScanType($scanType)
    {
        $this->scanType = $scanType;
    }

    /**
     * @return ProtectedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param ProtectedFile $file
     */
    public function setFile(ProtectedFile $file): void
    {
        $this->file = $file;

    }


    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->getExtension(), ['jpg', 'png', 'jpeg']);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->file) {
            return $this->file->getOriginalName();
        }

        return null;
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
     * @param string $originalName
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }

    /**
     * @return null|Tourist
     */
    public function getTourist()
    {
        return $this->tourist;
    }

    /**
     * @param Tourist $tourist
     */
    public function setTourist(Tourist $tourist = null)
    {
        $this->tourist = $tourist;
    }

    /**
     * @return null|Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param null|Organization $organization
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        if ($this->file) {
            return $this->file->getExtension();
        }

        return null;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        if ($this->file) {
            return $this->file->getMimeType();
        }

        return null;

    }

    /**
     * @see Organization
     * @see Tourist
     *
     * @return PayerInterface|null
     */
    public function getPayer()
    {
        if ($this->getOrganization()) {
            return $this->getOrganization();
        } elseif ($this->getTourist()) {
            return $this->getTourist();
        }

        return null;
    }

    /**
     * @return \MBH\Bundle\CashBundle\Document\CashDocument
     */
    public function getCashDocument()
    {
        return $this->cashDocument;
    }

    /**
     * @param mixed $cashDocument
    /* */
    public function setCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument = null)
    {
        $this->cashDocument = $cashDocument;
    }
}