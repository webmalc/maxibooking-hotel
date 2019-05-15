<?php
/**
 * Date: 24.04.19
 */

namespace MBH\Bundle\OnlineBundle\Services\MBSite\v2;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\OnlineBundle\Lib\image\ImageData;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

abstract class MbSiteData
{
    public const FILTER_SIZE_1980X1280 = 'size_1980x1280';
    public const FILTER_SIZE_500X500 = 'size_500x500';
    public const FILTER_THUMB_155x155 = 'thumb_155x155';
    public const FILTER_SCALER = 'scaler';

    abstract public function getPreparedData(): array;

    /**
     * @var UploaderHelper|null
     */
    protected $uploaderHelper;

    /**
     * @var CacheManager|null
     */
    protected $cacheManager;

    /**
     * DataManager constructor.
     * @param UploaderHelper $uploaderHelper
     * @param CacheManager $cacheManager
     */
    public function __construct(UploaderHelper $uploaderHelper, CacheManager $cacheManager)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->cacheManager = $cacheManager;
    }

    protected function generateUrl(Image $image, $filter = self::FILTER_SIZE_1980X1280): string
    {
        return $this->cacheManager->getBrowserPath($this->uploaderHelper->asset($image,'imageFile'), $filter);
    }

    /**
     * @param string $rawDesc
     * @return string|null
     */
    protected function stripTags(?string $rawDesc): ?string
    {
        $desc = strip_tags($rawDesc);

        return !empty($desc) ? $desc : null;
    }

    /**
     * @param Image[] | array
     * @return ImageData[] | array
     */
    protected function getImagesData(array $images, $isHotelData = false): array
    {
        $imagesData = [];

        if ($isHotelData) {
            $imagesData['top'] = [];
            $imagesData['gallery'] = [];
        }

        /** @var Image $image */
        foreach ($images as $image) {

            $imageData = new ImageData();
            $imageData
                ->setIsMain($image->getIsDefault())
                ->setUrl($this->generateUrl($image))
                ->setSmallUrl($this->generateUrl($image, self::FILTER_SIZE_500X500))
                ->setHeight($image->getHeight())
                ->setWidth($image->getWidth());

            if ($isHotelData) {
                if ($image->isMain()) {
                    $imagesData['top'][] = $imageData;
                } else {
                    $imagesData['gallery'][] = $imageData;
                }
            } else {
                $imagesData[] = $imageData;
            }
        }

        return $imagesData;
    }
}
