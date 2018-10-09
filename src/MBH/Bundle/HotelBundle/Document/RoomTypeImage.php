<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated
 * @ODM\EmbeddedDocument
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
     * @ODM\Field(type="string", name="name")
     */
    public $name;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="path")
     */
    public $path;

    /**
     * @var string
     * @Assert\File(
     *  maxSize = "5M",
     *  maxSizeMessage = "validator.document.roomTypeImage.image_size_5_mb")
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $image;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean", name="isMain")
     */
    protected $isMain = false;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="width")
     */
    public $width;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="height")
     */
    public $height;

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

        list($width, $height) = getimagesize($this->getUploadRootDir() . '/' . $newName);

        $this->setWidth($width);
        $this->setHeight($height);

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
        return null === $this->path ? null : $this->path;
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

    /**
     * Set width
     *
     * @param string $width
     * @return self
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Get width
     *
     * @return string $width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param string $height
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Get height
     *
     * @return string $height
     */
    public function getHeight()
    {
        return $this->height;
    }
}
