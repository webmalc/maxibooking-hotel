<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/** @ODM\EmbeddedDocument
 * @Gedmo\Loggable
 */

class RoomTypeImage
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @Gedmo\Versioned
     * @ODM\String(name="name")
     */
    public $name;

    /**
     * @Gedmo\Versioned
     * @ODM\String(name="path")
     */
    public $path;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $image;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isMain")
     */
    protected $isMain = 0;

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    public function uploadImage(\Symfony\Component\HttpFoundation\File\UploadedFile $image = null)
    {
        if (empty($image)) {
            return;
        }

        $this->image = null;
        $date = new \DateTime();

        $newName = $date->getTimestamp() . rand(1,1000) . '.'. $image->guessExtension();
        $image->move($this->getUploadRootDir(), $newName);


        $this->image = $newName;
        $this->setName($newName);
        $this->setPath($this->getUploadDir().'/'. $newName);
    }
    public function getUploadRootDir()
    {
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'upload/roomTypes';
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param bool $url
     * @return null|string
     */
    public function getImage($url = false)
    {
        if (empty($this->image) || !$url) {
            return $this->image;
        }
        $path = $this->getUploadRootDir() . '/' . $this->image;
        if (file_exists($path) && is_readable($path)) {
            return $this->getUploadDir() . '/' . $this->image;
        }

        return null;

    }

//    /**
//     * @return $this
//     */
//    public function imageDelete()
//    {
//        if (empty($this->image)) {
//            return $this;
//        }
//
//        $path = $this->getUploadRootDir() . '/' . $this->image;
//        if (file_exists($path) && is_readable($path)) {
//            unlink($this->getUploadDir() . '/' . $this->image);
//        }
//
//        $this->image = null;
//
//        return $this;
//    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isMain
     *
     * @param boolean $isMain
     * @return self
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;
        return $this;
    }

    /**
     * Get isMain
     *
     * @return boolean $isMain
     */
    public function getIsMain()
    {
        return $this->isMain;
    }
}
