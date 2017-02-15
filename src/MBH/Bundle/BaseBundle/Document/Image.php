<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ODM\Document(collection="Images")
 * @Vich\Uploadable
 */
class Image
{
    use TimestampableDocument;

    /** @ODM\Id */
    protected $id;

    /**
     * @var File
     * @Vich\UploadableField(mapping="upload_image", fileNameProperty="imageName")
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

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

//    public function uploadImage(UploadedFile $uploadedImage)
//    {
//        if (is_null($uploadedImage)) {
//            return;
//        }
//        $this->setName($uploadedImage->getClientOriginalName());
//        $uploadedImage->move($this->getUploadRootDir(), $uploadedImage->getClientOriginalName());
//        $this->setFile(self::HOTEL_UPLOAD_DIR.'/'. $uploadedImage->getClientOriginalName());
//    }
}