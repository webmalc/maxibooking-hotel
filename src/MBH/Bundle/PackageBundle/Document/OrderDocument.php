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
     * @Assert\File(maxSize="6M", mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     *          "application/pdf",
     *          "application/x-pdf",
     *          "application/msword",
     *          "application/xls",
     *          "application/xlsx",
     *          "application/vnd.ms-excel"
     * }, mimeTypesMessage="validator.document.OrderDocument.file_type")
     */
    protected $file;

    /**
     * Client Original Extension
     * @var string
     * @ODM\Field(type="string")
     */
    protected $extension;


    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $mimeType;

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
     * @return ProtectedFile|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param ProtectedFile $protectedFile
     */
    public function setFile(ProtectedFile $protectedFile = null)
    {
        $file = $protectedFile->getImageFile();
        $this->file = $protectedFile;
        if ($file && $file->isFile()) {
            $this->originalName = $protectedFile->getImageName();
            $this->name = uniqid().'.'.$file->getExtension();
            $this->extension = $file->getExtension();
            $this->mimeType = $file->getMimeType();
        }
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->extension, ['jpg', 'png', 'jpeg']);
    }

    /**
     * The absolute directory path where uploaded
     * documents should be saved
     * @param string|null $client
     * @return string
     */
    public function getUploadRootDir(string $client = null)
    {
        return __DIR__.'/../../../../../protectedUpload'.($client ? '/clients/'.$client : '').'/orderDocuments';
    }

    /**
     * @return string
     */
    public function getPath(string $client = null)
    {
        return $this->getUploadRootDir($client).DIRECTORY_SEPARATOR.$this->getName();
    }

    public function upload(string $client = null)
    {
        if (null === $this->getFile($client)) {
            return;
        }

        $this->getFile($client)->move($this->getUploadRootDir($client), $this->getName());
    }

    /**
     * @return bool
     */
    public function isUploaded(string $client = null)
    {
        return is_file($this->getPath($client));
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
     * @return bool
     */
    public function deleteFile(string $client = null)
    {
        if ($this->getFile($client) && is_writable($this->getFile($client)->getPathname())) {
            $result = unlink($this->getFile($client)->getPathname());
            if ($result) {
                $this->file = null;
            }

            return $result;
        }

        return false;
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
        return $this->extension;
    }

    /**
     * Client Original Extension
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
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