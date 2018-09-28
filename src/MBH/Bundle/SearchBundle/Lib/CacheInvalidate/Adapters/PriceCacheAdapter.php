<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters;


use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\AbstractInvalidateAdapter;

/**
 * Class PriceCacheAdapter
 * @package MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters
 * @property PriceCache $document
 */
class PriceCacheAdapter extends AbstractInvalidateAdapter
{
    public function getBegin(): \DateTime
    {
        return $this->document->getDate();
    }

    public function getEnd(): \DateTime
    {
        return $this->document->getDate();
    }

    public function getTariffIds(): ?array
    {
        return (array)$this->document->getTariff()->getId();
    }

    public function getRoomTypeIds(): ?array
    {
        $isUseCategory = $this->roomTypeManager->useCategories;
        $roomTypeIds = [];
        if ($isUseCategory) {
            $category = $this->document->getRoomTypeCategory();
            $roomTypes = $category->getTypes();
            foreach ($roomTypes as $roomType) {
                $roomTypeIds[] = $roomType->getId();
            }
        } else {
            $roomTypeIds = (array)$this->document->getRoomType();
        }

        return $roomTypeIds;
    }

}