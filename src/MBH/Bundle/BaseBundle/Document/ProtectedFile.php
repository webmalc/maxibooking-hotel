<?php


namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\HttpFoundation\File\File;
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
     *     maxSize = "2M", maxSizeMessage="validator.image.max_size_exceeded",
     *     mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
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
            list($width, $height) = getimagesize($image);
            $this->setWidth($width);
            $this->setHeight($height);

            $this->updatedAt = new \DateTimeImmutable();
        }

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

}