<?php


namespace MBH\Bundle\SearchBundle\Lib\CacheInvalidate;


interface InvalidateAdapterInterface
{
    public function getBegin(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getTariffIds(): ?array;

    public function getRoomTypeIds(): ?array;

    public function isMustInvalidateAfterUpdate(): bool;
}