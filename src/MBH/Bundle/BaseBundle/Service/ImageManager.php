<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\OnlineBundle\Exception\FailLoadPanoramaException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    private $projectDir;

    public function __construct(string $projectDir) {
        $this->projectDir = $projectDir;
    }

    /**
     * @param string $imageData
     * @param string $imageName
     * @return Image
     * @throws \Exception
     */
    public function saveFromBase64StringToFile(string $imageData, $imageName = 'image')
    {
        list(, $imageData) = explode(';', $imageData);
        list(, $imageData) = explode(',', $imageData);
        $imageData = base64_decode($imageData);

        $im = imagecreatefromstring($imageData);
        if ($im === false) {
            throw new FailLoadPanoramaException();
        }

        $path = $this->projectDir . '/web/upload/images/temp_image.jpeg';
        imagejpeg($im, $path);

        $imageFile = new UploadedFile($path, $imageName, 'image/jpg', null, null, true);

        return (new Image())->setImageFile($imageFile);
    }
}