<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters;


use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\AbstractInvalidateAdapter;

/**
 * Class RoomCacheAdapter
 * @package MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters
 * @property RoomCache $document
 */
class RoomCacheAdapter extends AbstractInvalidateAdapter
{
    public function getBegin(): ?\DateTime
    {
        return $this->document->getDate();
    }

    public function getEnd(): ?\DateTime
    {
        return $this->document->getDate();
    }

    public function getTariffIds(): ?array
    {
        return [];
    }

    public function getRoomTypeIds(): ?array
    {
        return (array) $this->document->getRoomType()->getId();
    }

}