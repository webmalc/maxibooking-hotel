<?php


namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class ProtectedFile
 * @package MBH\Bundle\BaseBundle\Document
 * @ODM\Document(collection="ProtectedFiles")
 * @Vich\Uploadable()
 * @Gedmo\Loggable()
 */
class ProtectedFile
{
    use TimestampableDocument;

    /** @ODM\Id */
    protected $id;

    /**
     * @var File
     * @Vich\UploadableField(mapping="protected_upload", fileNameProperty="imageName")
     * @Assert\File(
     *     maxSize = "6M", maxSizeMessage="validator.image.max_size_exceeded",
     *     mimeTypes={
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
     * }, mimeTypesMessage="validator.document.OrderDocument.file_type"
     * )
     */
    protected $imageFile;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $imageName;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $extension;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $originalName;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $height;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="width")
     */
    public $width;

    public function setImageFile(?File $image = null)
    {
        $this->imageFile = $image;

        if ($image && in_array($image->getType(), ['jpg', 'png', 'jpeg'])) {
            [$width, $height] = getimagesize($image);
            $this->setWidth($width);
            $this->setHeight($height);
        }

        $this->extension = $image->getExtension();
        $this->mimeType = $image->getMimeType();
        $this->originalName = $image->getBasename('.'. $this->extension);
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param string $imageName
     *
     * @return ProtectedFile
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ProtectedFile
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ProtectedFile
     */
    public function setDescription(string $description): Image
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight(): ?string
    {
        return $this->height;
    }

    /**
     * @param string $height
     * @return ProtectedFile
     */
    public function setHeight(string $height): ProtectedFile
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return ProtectedFile
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param bool $isDefault
     * @return ProtectedFile
     */
    public function setIsDefault(bool $isDefault): ProtectedFile
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param mixed $originalName
     */
    public function setOriginalName($originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param mixed $mimeType
     */
    public function setMimeType($mimeType): void
    {
        $this->mimeType = $mimeType;
    }



}