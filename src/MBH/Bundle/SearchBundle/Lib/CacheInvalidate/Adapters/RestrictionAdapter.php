<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate\Adapters;


use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\SearchBundle\Lib\CacheInvalidate\AbstractInvalidateAdapter;

/** @property Restriction $document */
class RestrictionAdapter extends AbstractInvalidateAdapter
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
        return (array)$this->document->getTariff()->getId();
    }

    public function getRoomTypeIds(): ?array
    {
        return (array)$this->document->getRoomType()->getId();
    }

}