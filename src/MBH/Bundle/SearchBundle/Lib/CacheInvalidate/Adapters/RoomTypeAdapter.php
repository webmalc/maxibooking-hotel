<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters;


use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\AbstractInvalidateAdapter;

/**
 * Class RoomType
 * @package MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters
 * @property RoomType $document
 */
class RoomTypeAdapter extends AbstractInvalidateAdapter
{
    public function getBegin(): ?\DateTime
    {
        return null;
    }

    public function getEnd(): ?\DateTime
    {
        return null;
    }

    public function getTariffIds(): ?array
    {
        return [];
    }

    public function getRoomTypeIds(): ?array
    {
        return (array)$this->document->getId();
    }

}