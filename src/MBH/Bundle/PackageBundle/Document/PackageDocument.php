<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ODM\MongoDB\Mapping\Annotations\PostRemove;

/**
 * @ODM\EmbeddedDocument
 * @ODM\HasLifecycleCallbacks
 * @author Aleksandr Arofikin
 */
class PackageDocument
{
    const TYPE_PASSPORT = 'passport';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';

    /**
     * @var string
     * @ODM\String
     */
    protected $type;

    /**
     * @var string
     * @ODM\String
     */
    protected $name;

    /**
     * @var string
     * @ODM\String
     */
    protected $originalName;

    /**
     * @var string
     * @ODM\String
     */
    protected $comment;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="6000000", mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     *          "application/pdf",
     *          "application/x-pdf",
     *          "application/msword"
     * })
     */
    protected $file;

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_PASSPORT,
            self::TYPE_INSURANCE,
            self::TYPE_BIRTH_CERTIFICATE,
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param mixed $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }

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
     * @return UploadedFile
     */
    public function getFile()
    {
        if(!$this->file && $this->name)
            $this->file = new UploadedFile($this->getUploadRootDir().DIRECTORY_SEPARATOR.$this->getName(), $this->getName());

        return $this->file;
    }

    public function getAsset()
    {
        return 'upload/packageDocuments/'.$this->name;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    /**
     * The absolute directory path where uploaded
     * documents should be saved
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/upload/packageDocuments';
    }

    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->originalName = $this->getFile()->getClientOriginalName();
        $this->name = uniqid().'.'.$this->getFile()->getClientOriginalExtension();

        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->name//$this->getFile()->getClientOriginalName()
        );

        //$this->name = $this->getFile()->getClientOriginalName();
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
     * @postRemove
     */
    public function postRemove()
    {
        $file = $this->getFile()->getPathname();

        if (file_exists($file) && is_writable($file))
        {
            unlink($file);
        }
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
}