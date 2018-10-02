<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters;


use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\AbstractInvalidateAdapter;

/**
 * Class TariffAdapter
 * @package MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters
 * @property Tariff $document
 */
class TariffAdapter extends AbstractInvalidateAdapter
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
        return (array)$this->document->getId();
    }

    public function getRoomTypeIds(): ?array
    {
        return [];
    }

}